# AGENTS

## Release Process

1. Bump the version in `package.json`.
2. Add the release notes to the top of the changelog in `readme.md`.
3. Run the release task with `bun run release`.
4. The release task updates plugin version metadata, refreshes the stable tag, builds the plugin, prepares `release/<version>/`, `release/latest/`, and `release/svn/`, and deploys to WordPress.org SVN.

## Notes

- `package.json` is the release source of truth.
- Agents should prefer `bun` over `npm` for project commands.
- Agents must never run `bun run release` themselves.
- `AGENTS.md` is intentionally excluded from release artifacts in `Gruntfile.js`.
