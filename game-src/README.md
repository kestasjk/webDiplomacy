# The game board

A copy of `beta-src/` that builds to `/game/` instead of `/beta/`, and reads games only from their public
JSON files and the `game/playercontext` route, described in `doc/gamedata/02-spec.md`. It is here to replace
the beta board once it has had enough play.

`src/utils/api/gameSource.ts` is where that difference lives: it fetches the files and the context, remembers
which version of each file it holds, and hands the rest of the app the same shapes the old routes returned.
`src/lib/sselistener.ts` listens for the `files` event, which says which files changed, and re-fetches only
those. Orders, messages and votes are still sent to the write routes in `api.php`.

## Build for production

```
npm run build:production
```
