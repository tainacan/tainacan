# Releasing a new version

This is a work in progress documentaion on how to release a new version.

Assuming:

* $CURRENT_VERSION is the current "old" version (e.g. 0.2)
* $NEW_VERSION is the version we are releasing (e.g. 0.3)
* $GIT_PATH is where our repository is cloned
* $BUILD_PATH is where the plugin is condigured to buid 
* $SVN_PATH is where the WordPress.org SVN repo is

## Pre-release

Before we publish a new version, we always release one or more Release Candidates so the community have time to test and make sure the new version of Tainacan is clean and ready to reach the world.

### Start in the git repository

```
cd $GIT_PATH
git checkout develop
git pull
```

### Create a new Release branch

Create a new `release/$NEW_VERSION` branch. If using git flow:

```
git flow release start $NEW_VERSION
```

### Edit version numbers

Edit `src/readme.txt` and `src/tainacan.php` and change the version numbers to `$NEW_VERSION`. In `tainacan.php` also change the `TAINACAN_VERSION` constant after the comments section

When releasing a RC version, append RC (number) to the version.

Also increase the `Tested Up` version, if applicable.

### Set build to production mode

Edit `webpack.config.js` to set production mode.

### Build

```
./build.sh
cd $BUILD_PATH
```
### Set build to development mode

```
cd $GIT_PATH
git checkout webpack.config.js
```

### Publish the RC

Create a ZIP package with the built plugin and publish a blog post calling for tests.

Commit and push to Git!

### Test

Using the Test guide as a resource, test every feature of the plugin, including importers and exporters. With time, we can build more detailed test scripts and distribute tests among people in the community.


### Fix and publish new RC, or finish

If bugs are found, fix them and commit to the release branch. Publish a new RC, following the steps above, until we have a stable version we feel confident to release.

## Release

The plugin is ready to go. We have published one or more RCs and the community have tested it. Lets get it live to the world!

### Finish version number

Back to the git repository, make sure everything is up to date:

```
cd $GIT_PATH
git checkout release/$NEW_VERSION
git pull
```
Edit `src/readme.txt` and `src/tainacan.php` and change the version numbers to `$NEW_VERSION`.

Commit and push.

### Set build to production mode

Edit `webpack.config.js` to set production mode.

### Build

```
./build.sh
cd $BUILD_PATH
```
### Set build to development mode

```
cd $GIT_PATH
git checkout webpack.config.js
```

### Prepare SVN repo

clean trunk

```
rm -rf $SVN_PATH/trunk/*
```

### Copy new files

```
cp -R $BUILD_PATH/* $SVN_PATH/trunk/
```

Update assets

```
cp $GIT_PATH/wp-repo-assets/* $SVN_PATH/assets/
```

Create tag folder

```
cd $SVN_PATH
mkdir tags/$NEW_VERSION
cp -R trunk/* tags/$NEW_VERSION/
svn add tags/$NEW_VERSION
```

### Finish and commit

Go to the SVN folder

```
cd $SVN_PATH
```

`svn rm` all files that have been removed

```
svn st | grep '^!' | awk '{print $2}' | xargs svn rm
```

`svn add` all new files

```
svn st | grep '^?' | awk '{print $2}' | xargs svn add
```

Commit!

```
svn ci
```

### Check

In few minutes the new release should be available in the WordPress directory. 

Check if everything is ok.

### Commit and create tag on git

Once the release is tested and confirmed, commit and create the tag on git.

Merge the release branch into master and develop branches and create a tag.

Using git flow:

```
cd $GIT_PATH

git flow release finish $NEW_VERSION
git push --all
git push --tags
```



