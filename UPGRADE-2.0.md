# UPGRADE FROM 1.0 to 2.0

```diff
{
  "name": "foo/bar",
  "require": {
-    "pact-foundation/composer-downloads-plugin": "^1.0"
+    "pact-foundation/composer-downloads-plugin": "^2.0"
  },
  "extra": {
    "downloads": {
      "examplelib": {
-        "url": "https://example.com/file.ext",
+        "url": "https://example.com/new-file.ext",
        "path": "files/file.txt"
      }
    }
  }
}
```

Because of tracking file has been changed, `https://example.com/new-file.ext` will not be downloaded. There will
be an warning on your terminal:

```
Extra file foo/bar:examplelib has been locally overriden in files/file.txt. To reset it, delete and reinstall.
```

You need to run **one** of these commands:
* `composer reinstall foo/bar`
* `rm vendor/foo/bar/files/file.txt & composer update foo/bar`
