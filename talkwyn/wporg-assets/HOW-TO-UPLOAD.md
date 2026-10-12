# Publish Talkwyn on WordPress.org

This folder has the same layout as your SVN repository:

```
trunk/          the plugin, exactly the approved 2.3.13 files
tags/2.3.13/    the same files, frozen as release 2.3.13
assets/         banners, icons and the 6 screenshots for the plugin page
```

`assets/` is not part of the plugin ZIP. WordPress.org only shows it on the page.

## Before you start

1. Wait up to 1 hour after the approval email, until SVN access is active.
2. Set your SVN password: https://profiles.wordpress.org/talkwyn/profile/edit/group/3/?screen=svn-password
3. Your SVN username is `talkwyn`. It is case sensitive and is not your email address.

## Option A: Windows with TortoiseSVN

1. Install TortoiseSVN from https://tortoisesvn.net/
2. Create an empty folder, for example `C:\talkwyn-svn`.
3. Right-click it, choose **SVN Checkout**, enter `https://plugins.svn.wordpress.org/talkwyn` and click OK. You get empty `trunk`, `tags`, `branches` and `assets` folders.
4. Copy the contents of this package's `trunk`, `tags` and `assets` folders into the matching folders. Do not include `HOW-TO-UPLOAD.md`.
5. Right-click `C:\talkwyn-svn`, choose **TortoiseSVN > Add**, select every new file and click OK.
6. Right-click again, choose **SVN Commit**, enter the message `Release 2.3.13`, then log in with `talkwyn` and your SVN password.

## Option B: Mac or Linux terminal

```
svn checkout https://plugins.svn.wordpress.org/talkwyn talkwyn-svn
cp -R trunk/. talkwyn-svn/trunk/
mkdir -p talkwyn-svn/tags/2.3.13 && cp -R tags/2.3.13/. talkwyn-svn/tags/2.3.13/
cp -R assets/. talkwyn-svn/assets/
cd talkwyn-svn
svn add --force trunk tags assets
svn propset svn:mime-type image/png assets/*.png
svn commit -m "Release 2.3.13" --username talkwyn
```

## After the commit

- The page https://wordpress.org/plugins/talkwyn/ appears within about 15 minutes. Search results and the "Install" button in wp-admin can take up to 72 hours.
- Check the page shows version 2.3.13, the banner, the icon and all 6 screenshots.
- In the talkwyn.com theme, open **Appearance > Site Settings** and fill **WordPress.org plugin URL** with `https://wordpress.org/plugins/talkwyn/`. The download page then shows "Get it on WordPress.org".
- The "Requires Plugins: talkwyn" note in Talkwyn Pro now resolves, because the free plugin exists on WordPress.org.

## Releasing an update later (for example 2.3.14)

1. Copy the new plugin files into `trunk/`. Make sure `Stable tag: 2.3.14` is in `trunk/readme.txt` and `Version: 2.3.14` is in `talkwyn.php`.
2. Copy `trunk` to the new tag: `svn copy trunk tags/2.3.14`
3. Commit with the message `Release 2.3.14`.

Never edit a folder inside `tags/` after you commit it. A new version always gets a new tag.
