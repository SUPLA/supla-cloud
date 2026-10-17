# Issue tracker: Local Markdown

Issues and specs for this repo live as markdown files in `.scratch/`.

## Conventions

- One feature per directory: `.scratch/<feature-slug>/`
- The spec is `.scratch/<feature-slug>/spec.md`
- Implementation issues are one file per ticket at `.scratch/<feature-slug>/issues/<NN>-<slug>.md`, numbered from `01`, never a single combined tickets file
- Ticket state is recorded as a `Status:` line near the top of each issue file: `ready-for-agent`, `in-progress`, or `done`
- Comments and conversation history append to the bottom of the file under a `## Comments` heading

## Working the ticket queue

- A ticket is **implementable** when its status is `ready-for-agent` and every ticket named in its `Blocked by:` line has status `done`.
- The set of implementable tickets is the **frontier**. Work any frontier ticket; use the lowest ticket number when choosing among otherwise equivalent tickets.
- Before implementation, set the chosen ticket to `in-progress`. After the implementation is complete, reviewed, and committed, set it to `done`.
- A ticket with `Blocked by: None (can start immediately).` is on the frontier whenever it is `ready-for-agent`.

## When a skill says "publish to the issue tracker"

Create a new file under `.scratch/<feature-slug>/` (creating the directory if needed).

## When a skill says "fetch the relevant ticket"

Read the file at the referenced path. The user will normally pass the path or the issue number directly.

## Wayfinding operations

Used by `/wayfinder`. The **map** is a file with one **child** file per ticket.

- **Map**: `.scratch/<effort>/map.md` (the Notes / Decisions-so-far / Fog body).
- **Child ticket**: `.scratch/<effort>/issues/NN-<slug>.md`, numbered from `01`, with the question in the body. A `Type:` line records the ticket type (`research`/`prototype`/`grilling`/`task`); a `Status:` line records `claimed`/`resolved`.
- **Blocking**: a `Blocked by: NN, NN` line near the top. A ticket is unblocked when every file it lists is `resolved`.
- **Frontier**: scan `.scratch/<effort>/issues/` for files that are open, unblocked, and unclaimed; first by number wins.
- **Claim**: set `Status: claimed` and save before any work.
- **Resolve**: append the answer under an `## Answer` heading, set `Status: resolved`, then append a context pointer (gist + link) to the map's Decisions-so-far in `map.md`.
