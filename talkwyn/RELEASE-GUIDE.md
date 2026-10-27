# How to release a new Talkwyn version

Use this every time Claude sends you a new free plugin version.
In the steps, `X.Y.Z` means the new version number, for example `2.3.15`.

## What you get from Claude

- `talkwyn-wporg-svn-X.Y.Z.zip`: for WordPress.org (SVN)
- `talkwyn-X.Y.Z.zip`: for talkwyn.com (Talkwyn Hub)

## Part 1: WordPress.org (TortoiseSVN)

### Step 1: Get the latest copy
Right click your SVN folder (the one with `trunk`, `tags`, `assets`), then **SVN Update**.

### Step 2: Unzip the new version
Unzip `talkwyn-wporg-svn-X.Y.Z.zip` somewhere else, for example on the Desktop.
Inside are `trunk`, `tags/X.Y.Z` and `HOW-TO-UPDATE.md`.

### Step 3: Delete old files (only if HOW-TO-UPDATE.md lists any)
In your SVN `trunk`, right click each listed file, then **TortoiseSVN > Delete**.
Never delete with the normal Delete key.

### Step 4: Replace trunk
Copy everything inside the unzipped `trunk` into your SVN `trunk`.
Choose **Replace** for all files.

### Step 5: Add the new tag
Copy the unzipped `tags/X.Y.Z` folder into your SVN `tags` folder.
Right click the new `X.Y.Z` folder, then **TortoiseSVN > Add**, select all, OK.

### Step 6: Add any new files in trunk
Right click your SVN `trunk`, then **TortoiseSVN > Add**.
If a list appears, select all and click OK. If it says nothing to add, that is fine.

### Step 7: Commit
Right click the main SVN folder, then **SVN Commit**.
- Message: `Release X.Y.Z`
- Check the list before you click OK:
  - files in `tags/X.Y.Z` show **Added**
  - changed files in `trunk` show **Modified**
  - files from Step 3 show **Deleted**
- If you only see "Modified" and nothing "Added", stop. Go back to Step 5.

### Step 8: Check
After 15 to 30 minutes open https://wordpress.org/plugins/talkwyn/
The right side should show **Version X.Y.Z**.

## Part 2: talkwyn.com (Talkwyn Hub)

### Step 9: Upload the zip
1. Log in to talkwyn.com/wp-admin.
2. Open **Talkwyn Hub > Releases**.
3. Add a new release for the free plugin, upload `talkwyn-X.Y.Z.zip`, channel **Stable**, and save.
4. Open talkwyn.com/download/ and click the download button. The file should be `talkwyn-X.Y.Z.zip`.

### Step 10: Update the plugin on talkwyn.com
When WordPress.org shows the new version, go to **Dashboard > Updates** on talkwyn.com and update Talkwyn.

## Common mistakes

| Problem | Fix |
|---|---|
| WordPress.org still shows the old version | The tag folder was not added. Do Step 5 again, then commit. |
| Commit shows only "Modified" | New files were not added. Do Steps 5 and 6, then commit. |
| "Out of date" error on commit | Do **SVN Update** on the main folder, then commit again. |
| A file shows a red "!" in the folder | It was deleted the normal way. Right click it, **TortoiseSVN > Delete**, then commit. |
