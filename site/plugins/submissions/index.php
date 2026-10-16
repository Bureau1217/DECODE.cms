<?php

/**
 * Public write endpoint for the Bot / Troll Detector's "review table":
 * when a visitor records their own verdict on a demo account, this
 * creates a `submission` child page under tools/bot-troll-detector
 * (site/blueprints/pages/submission.yml) so it's visible to editors in
 * the panel — the CMS-side "results of the game" DECODE.webapp writes to
 * via its own /api/submit-verdict proxy (server/api/submit-verdict.post.ts).
 *
 * Deliberately a plain Kirby route, not a `kql`/`api()` one — this must
 * stay public regardless of `kql.auth`/`api.basicAuth` (unrelated,
 * read-side concerns). `impersonate('kirby')` is what lets an
 * unauthenticated visitor's request create a page at all; everything
 * written is plain text content (no blocks, no HTML fields), so there's
 * nothing here for a submitted string to execute.
 */

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;

const DECODE_VERDICT_LABELS = [
	'legitimate' => 'Legitimate',
	'suspicious' => 'Suspicious',
	'bot_troll'  => 'Likely bot / troll',
];

App::plugin('decode/submissions', [
	'pageMethods' => [
		// Human-readable verdict for the panel's submissions table
		// (bot-troll-tool.yml) and the CSV export below.
		'verdictLabel' => function () {
			$value = $this->verdict()->value();
			return DECODE_VERDICT_LABELS[$value] ?? $value;
		},
	],
	'routes' => function (App $kirby) {
		return [
			[
				// CSV of every visitor verdict, newest first — linked from the
				// "Soumissions des visiteurs" tab (bot-troll-tool.yml). Panel
				// users only: the panel session cookie is shared with this
				// same-origin route, so a logged-in editor just clicks it.
				'pattern' => 'submissions/bot-troll-detector.csv',
				'method'  => 'GET',
				'action'  => function () use ($kirby) {
					if (!$kirby->user()) {
						return new Response('Log in to the panel first.', 'text/plain', 403);
					}

					$parent = $kirby->page('tools/bot-troll-detector');
					$submissions = $parent
						? $parent->childrenAndDrafts()->filterBy('intendedTemplate', 'submission')->sortBy('reviewed_at', 'desc')
						: [];

					$out = fopen('php://temp', 'r+');
					// BOM so Excel opens accents (é, ·) as UTF-8.
					fwrite($out, "\xEF\xBB\xBF");
					fputcsv($out, ['Date', 'Compte', 'Plateforme', 'Verdict', 'Commentaire'], ',', '"', '');
					foreach ($submissions as $submission) {
						/** @var Page $submission */
						fputcsv($out, [
							$submission->reviewed_at()->toDate('Y-m-d H:i'),
							$submission->account_handle()->value(),
							$submission->account_platform()->value(),
							$submission->verdictLabel(),
							$submission->note()->value(),
						], ',', '"', '');
					}
					rewind($out);
					$csv = stream_get_contents($out);
					fclose($out);

					return new Response($csv, 'text/csv', 200, [
						'Content-Disposition' => 'attachment; filename="bot-troll-submissions-' . date('Y-m-d') . '.csv"',
					]);
				}
			],
			[
				// Not under /api/... — that path prefix is reserved for
				// Kirby's own API router (where the kql plugin lives) and
				// a plain route there never gets reached.
				'pattern' => 'submit-verdict',
				'method'  => 'POST',
				'action'  => function () use ($kirby) {
					$data = $kirby->request()->body()->toArray();

					$allowedVerdicts = ['legitimate', 'suspicious', 'bot_troll'];
					$verdict = $data['verdict'] ?? null;

					if (in_array($verdict, $allowedVerdicts, true) === false) {
						return [
							'code'   => 400,
							'status' => 'error',
							'message' => 'Invalid or missing verdict.',
						];
					}

					$handle = Str::slug(trim((string)($data['account_handle'] ?? '')));
					if ($handle === '') {
						return [
							'code'   => 400,
							'status' => 'error',
							'message' => 'account_handle is required.',
						];
					}

					$confidence = (int)($data['confidence'] ?? 0);
					$confidence = max(0, min(100, $confidence));

					$note = Str::short(trim((string)($data['note'] ?? '')), 500);

					$result = $kirby->impersonate('kirby', function () use ($kirby, $handle, $data, $verdict, $confidence, $note) {
						$parent = $kirby->page('tools/bot-troll-detector');
						if (!$parent) {
							return null;
						}

						$slug = 'sub-' . $handle . '-' . substr(bin2hex(random_bytes(4)), 0, 8);

						return $parent->createChild([
							'slug'     => $slug,
							'template' => 'submission',
							'content'  => [
								'title'            => $verdict . ' — ' . (string)($data['account_handle'] ?? ''),
								'account_handle'   => (string)($data['account_handle'] ?? ''),
								'account_platform' => (string)($data['account_platform'] ?? ''),
								'verdict'          => $verdict,
								'confidence'       => $confidence,
								'note'             => $note,
								'reviewed_at'      => date('c'),
							],
						])->changeStatus('unlisted');
					});

					if (!$result) {
						return [
							'code'   => 500,
							'status' => 'error',
							'message' => 'Could not save submission.',
						];
					}

					return [
						'code'   => 200,
						'status' => 'ok',
					];
				}
			]
		];
	}
]);
