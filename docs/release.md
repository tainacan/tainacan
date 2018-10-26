# Releasing a new version

This is a work in progress documentaion on how to release a new version.

Assuming:

* $CURRENT_VERSION is the current "old" version (e.g. 0.2)
* $NEW_VERSION is the version we are releasing (e.g. 0.3)
* $GIT_PATH is where our repository is cloned
* $BUILD_PATH is where the plugin is condigured to buid 
* $SVN_PATH is where the WordPress.org SVN repo is


### Start in the git repository

```
cd $GIT_PATH
git checkout develop
git pull
```

### Edit version numbers

Edit `src/readme.txt` and 'src/tainacan.php' and change the version numbers to `$NEW_VERSION`.

### Set build to production mode

Edit `webpack.config.js` to set production mode.

### Commit and create tag

```
git commit -am "Releasing verion $NEW_VERSION"
```

### Build and cleanup 

```
./build.sh
cd $BUILD_PATH
rm admin/scss/.sass-cache
```

### Prepare SVN repo

Create tag with the old version

```
svn cp https://plugins.svn.wordpress.org/tainacan/trunk https://plugins.svn.wordpress.org/tainacan/tags/$OLD_VERSION
```

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


### Finish and commit

Go to the SVN folder

```
cd $SVN_PATH/trunk
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

### Create tag on git

```
git tag $NEW_VERSION
git checkout master
git merge develop
git push --all
git push --tags
```

### Set build back to development mode

Once you go back to develop branch, remember editing `webpack.config.js` to set production mode.


