# WP WAF Manager Fork Maintenance Workflow

# WP WAF Manager Fork Update Workflow

## Branch Rules

```text
main
= clean mirror of upstream only

feature/extensible-rule1-allowlist
= small feature patch only

release/wpwaf-manager-fork
= production fork branch with updater, deploy files, UUPD, version bumps
```

Never reset the release branch to the feature branch.

Do not use:

```bash
git reset --hard feature/extensible-rule1-allowlist
```

on the release branch.

------

# 1. Sync `main` With Upstream

You should be on any branch to start, but this step switches to `main`.

```bash
git switch main
git fetch upstream
git reset --hard upstream/main
git push origin main --force-with-lease
```

------

# 2. Rebase the Feature Patch

Switch to the feature branch:

```bash
git switch feature/extensible-rule1-allowlist
```

Rebase it onto the latest upstream:

```bash
git rebase upstream/main
```

If conflicts happen, fix them, then:

```bash
git add path/to/conflicted-file.php
GIT_EDITOR=true git rebase --continue
```

When complete, push the rewritten feature branch:

```bash
git push origin feature/extensible-rule1-allowlist --force-with-lease
```

Do not use `git pull` here.

------

# 3. Merge Feature Into Release Branch

Switch to the release branch:

```bash
git switch release/wpwaf-manager-fork
```

Merge the feature branch:

```bash
git merge feature/extensible-rule1-allowlist
```

If conflicts happen, fix them manually.

Important files to preserve on the release branch:

```text
deploy.sh
deploy.cfg
merge.md
uupd/
includes/updater.php
includes/stingray82.php
```

In `wpwafmanager.php`, make sure these remain present:

```php
define( 'WPWAF_FILE', __FILE__ );
require_once __DIR__ . '/includes/stingray82.php';
```

Also make sure the plugin header version and `WPWAF_VERSION` match the intended fork release version.

After resolving conflicts:

```bash
git add wpwafmanager.php includes/class-rule-builder.php includes/stingray82.php
```

Or, if you are sure all changes are correct:

```bash
git add .
```

Commit the merge:

```bash
git commit -m "Merge feature patch into release branch"
```

Do not run `git merge` again while Git says `MERGING`.

------

# 4. Safety Checks Before Deploy

Check status:

```bash
git status
```

Search for unresolved conflict markers:

```bash
grep -R "<<<<<<<\|=======\|>>>>>>>" .
```

Review the merge commit:

```bash
git show --stat --oneline HEAD
git show --name-status HEAD
```

If anything looks wrong, stop and inspect before deploying.

------

# 5. Push Release Branch

```bash
git push origin release/wpwaf-manager-fork
```

If Git rejects because history was rewritten, only then use:

```bash
git push origin release/wpwaf-manager-fork --force-with-lease
```

------

# 6. Deploy

Make sure `deploy.cfg` is set correctly.

For test build:

```ini
DRY_RUN=1
```

For live release:

```ini
DRY_RUN=0
```

Run:

```bash
sh deploy.sh
```

------

# Common Mistakes

## Wrong push command

Wrong:

```bash
git push feature/extensible-rule1-allowlist --force-with-lease
```

Correct:

```bash
git push origin feature/extensible-rule1-allowlist --force-with-lease
```

## Do not pull after a rebase

Wrong:

```bash
git pull
```

Correct:

```bash
git push origin feature/extensible-rule1-allowlist --force-with-lease
```

## Do not reset release to feature

Wrong:

```bash
git reset --hard feature/extensible-rule1-allowlist
```

Correct:

```bash
git merge feature/extensible-rule1-allowlist
```

## If Git editor breaks

Use:

```bash
GIT_EDITOR=true git rebase --continue
```

or commit with a message:

```bash
git commit -m "Merge feature patch into release branch"
```

Optional permanent fix:

```bash
git config --global core.editor true
```

------

# Short Version

```bash
git switch main
git fetch upstream
git reset --hard upstream/main
git push origin main --force-with-lease

git switch feature/extensible-rule1-allowlist
git rebase upstream/main
git push origin feature/extensible-rule1-allowlist --force-with-lease

git switch release/wpwaf-manager-fork
git merge feature/extensible-rule1-allowlist

# fix conflicts if needed
git add .
git commit -m "Merge feature patch into release branch"

grep -R --exclude-dir=.git "<<<<<<<\|>>>>>>>" .
git push origin release/wpwaf-manager-fork

sh deploy.sh
```
