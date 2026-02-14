# Agent Instructions

## TASK.md

The `TASK.md` file describes the task being performed and should be kept updated as work progresses. This file serves as a living record of:

- What is being built or fixed
- Current progress and status
- Remaining work items
- Any important context or decisions

All work on this repository should begin by reading and understanding `TASK.md`. Whenever any significant progress is made, `TASK.md` should be updated to reflect the current state of work.

**Note:** When you receive the prompt "Start the task", `TASK.md` is already in your context. DO NOT re-read it — instead, proceed directly with the work described in the task.

## Commit Messages

When creating commit messages, do not reference changes to `TASK.md`, `AGENTS.md`, or any files tracked in `agency.json` (such as `opencode.json`). These are project management and configuration files that should not be mentioned in commit messages. Focus commit messages on actual code changes, features, fixes, and refactoring.

**Important:** Even when the only changes in a commit are to tracked files like `TASK.md`, you should still commit those changes. These updates should be co-located with the code changes they describe. Simply omit mentioning the tracked files in the commit message and focus the message on the actual code changes being made.
