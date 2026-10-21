// Runs .agents/plugins/repo-guard/hooks/guard.py before every tool call, so OpenCode
// enforces the same "Never" rules as Claude Code.
import { spawnSync } from "node:child_process"
import { join } from "node:path"
import type { Plugin } from "@opencode-ai/plugin"

export const RepoGuard: Plugin = async ({ worktree, directory }) => {
  const guard = join(worktree ?? directory, ".agents/plugins/repo-guard/hooks/guard.py")
  return {
    "tool.execute.before": async (input, output) => {
      const run = spawnSync("python3", [guard], {
        input: JSON.stringify({ tool_name: input.tool, tool_input: output.args ?? {} }),
        encoding: "utf-8",
        timeout: 10_000,
      })
      if (run.status === 2) throw new Error(run.stderr.trim())
    },
  }
}
