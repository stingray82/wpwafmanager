# WP WAF Manager Fork Maintenance Workflow

Original upstream repo:

```text
https://github.com/jaimealnassim/wpwafmanager
```

fork/release repo:

```text
https://github.com/stingray82/wpwafmanager
```

---

## Branch Structure

```text
main
  Clean upstream mirror only.
  Do not make custom fork edits here.

feature/extensible-rule1-allowlist
  Original untouched PR branch.
  Leave this alone unless the upstream PR needs updating.

patch/wpwaf-custom-filters
  Working patch branch containing your maintained fork changes.

release/wpwaf-manager-fork
  Deployment branch used by live sites and deploy.sh.
```

---

# One-Time Setup

## Branch you can be on

You can run this from any branch.

## Add upstream remote

```bash
git remote add upstream https://github.com/jaimealnassim/wpwafmanager.git
git fetch upstream
```

Check remotes:

```bash
git remote -v
```

Expected idea:

```text
origin    https://github.com/stingray82/wpwafmanager.git
upstream  https://github.com/jaimealnassim/wpwafmanager.git
```

---

# 1. Sync `main` With Original Plugin

## Branch you should be on

```text
main
```

## Commands

```bash
git checkout main
git fetch upstream
git reset --hard upstream/main
git push origin main --force-with-lease
```

## What this does

This makes your `main` branch match the original plugin.

Do not put custom filters, updater code, or deployment changes directly on `main`.

---

# 2. Rebase the Patch Branch Onto Latest Upstream

## Branch you should be on

Start by switching to:

```text
 feature/extensible-rule1-allowlist
```

## Commands

```bash
git checkout  feature/extensible-rule1-allowlist
git fetch upstream
git rebase upstream/main
```

## If there are conflicts

Stay on:

```text
 feature/extensible-rule1-allowlist
```

Then check what needs resolving:

```bash
git status
```

After fixing the files:

```bash
git add .
git rebase --continue
```

If the rebase goes badly and you want to cancel it:

```bash
git rebase --abort
```

## Push the updated patch branch

Still on:

```text
 feature/extensible-rule1-allowlist
```

Run:

```bash
git push feature/extensible-rule1-allowlist --force-with-lease
```

---

# 3. Update the Release Branch From the Patch Branch

## Branch you should be on

Switch to:

```text
release/wpwaf-manager-fork
```

## Commands

```bash
git checkout release/wpwaf-manager-fork
git reset --hard patch/wpwaf-custom-filters
git push origin release/wpwaf-manager-fork --force-with-lease
```

## What this does

This makes the release branch identical to your tested patch branch.

Live updater metadata should come from this branch.

Fix files

```
git add wpwafmanager.php
GIT_EDITOR=true git rebase --continue
```

Then 

```
git status
git push origin release/wpwaf-manager-fork --force-with-lease
sh deploy.sh
```



---

# 4. Deploy From the Release Branch

## Branch you must be on

```text
release/wpwaf-manager-fork
```

Check with:

```bash
git branch --show-current
```

It should return:

```text
release/wpwaf-manager-fork
```

---

## Dry run deployment

Make sure `deploy.cfg` has:

```ini
DRY_RUN=1
```

Then, while on:

```text
release/wpwaf-manager-fork
```

Run:

```bash
sh deploy.sh
```

This should:

- build the plugin zip
- generate `uupd/index.json`
- check the zip contents
- avoid committing
- avoid pushing
- avoid creating a GitHub release

---

## Live deployment

Make sure you are still on:

```text
release/wpwaf-manager-fork
```

Set `deploy.cfg` to:

```ini
DRY_RUN=0
```

Then run:

```bash
sh deploy.sh
```

This should:

- build the plugin zip
- generate `uupd/index.json`
- commit generated files
- push the release branch
- create or update the GitHub Release
- upload the release zip

---

# UUPD Architecture

## Files included in the plugin zip

```text
includes/updater.php
includes/stingray82.php
```

## Files excluded from the plugin zip

```text
uupd/
deploy.sh
deploy.cfg
cl.txt
static.txt
```

---

# Updater Endpoint

The updater JSON should be served from the release branch:

```text
https://raw.githubusercontent.com/stingray82/wpwafmanager/release/wpwaf-manager-fork/uupd/index.json
```

The plugin zip should be served from the latest GitHub Release asset:

```text
https://github.com/stingray82/wpwafmanager/releases/latest/download/wp-waf-manager.zip
```

This is correct even though GitHub shows releases at repo level.

The release should be created while targeting:

```text
release/wpwaf-manager-fork
```

---

# Versioning Strategy

Use the upstream version plus your fork patch number.

Examples:

```text
Upstream: 1.0.6
Fork:     1.0.6.1

Upstream: 1.0.7
Fork:     1.0.7.1

Upstream: 1.0.7
Second fork patch: 1.0.7.2
```

Update both:

```text
Plugin header Version
WPWAF_VERSION constant
```

---

# Recommended Git Config

You can run this from any branch.

```bash
git config --global rerere.enabled true
```

This helps Git remember repeated conflict resolutions during future rebases.

---

# Full Update Checklist

## Step 1: Sync main

Branch:

```text
main
```

Commands:

```bash
git checkout main
git fetch upstream
git reset --hard upstream/main
git push origin main --force-with-lease
```

---

## Step 2: Rebase patch branch

Branch:

```text
patch/wpwaf-custom-filters
```

Commands:

```bash
git checkout patch/wpwaf-custom-filters
git rebase upstream/main
git push origin patch/wpwaf-custom-filters --force-with-lease
```

---

## Step 3: Update release branch

Branch:

```text
release/wpwaf-manager-fork
```

Commands:

```bash
git checkout release/wpwaf-manager-fork
git reset --hard patch/wpwaf-custom-filters
git push origin release/wpwaf-manager-fork --force-with-lease
```

---

## Step 4: Dry run deploy

Branch:

```text
release/wpwaf-manager-fork
```

Config:

```ini
DRY_RUN=1
```

Command:

```bash
sh deploy.sh
```

---

## Step 5: Live deploy

Branch:

```text
release/wpwaf-manager-fork
```

Config:

```ini
DRY_RUN=0
```

Command:

```bash
sh deploy.sh
```

---

# Daily Rules

## Only sync upstream on:

```text
main
```

## Only edit fork patches on:

```text
patch/wpwaf-custom-filters
```

## Only deploy from:

```text
release/wpwaf-manager-fork
```

## Keep untouched unless updating the PR:

```text
feature/extensible-rule1-allowlist
```





Example FIlter

```
add_filter( 'wpwaf_rule1_extra_allow_expressions', function( array $expressions ): array {
	$expressions[] = 'http.request.headers["x-trustwards-scanner"][0] eq "true"';

	return $expressions;
} );
```





```
git fetch upstream
git checkout feature/extensible-rule1-allowlist
git rebase upstream/main
# fix only if needed

git checkout release/wpwaf-manager-fork
git rebase feature/extensible-rule1-allowlist
git push origin release/wpwaf-manager-fork --force-with-lease
sh deploy.sh
```

