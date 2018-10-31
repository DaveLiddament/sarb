# Unified diff format terminology

This document outlines terminology surrounding diffs is as per [Unified format](https://en.wikipedia.org/wiki/Diff#Unified_format)
.




## Terminology

See example below:

```
diff --git a/src/User.php b/src/Person.php
similarity index 93%
rename from src/User.php
rename to src/Person.php
index a86c17c..3d4462c 100644
--- a/src/User.php
+++ b/src/Person.php
@@ -6,7 +6,7 @@ declare(strict_types=1);
 namespace CodeDiff;


-class User
+class Person
 {

     /**
@@ -20,7 +20,7 @@ class User
     private $isAdmin;

     /**
-     * User constructor.
+     * Person constructor.
      * @param string $name
      * @param bool $isAdmin
      */
```

The following terminology is used:

- **original file** refers to the file before the change (in this case `src/User.php`)
- **new file** refers to the file after the change (in this case `src/Person.php`)
- **change hunks** contains the difference between the 2 files. Change hunks start at the line beginning with `@@`.
In the above example there are 2 change hunks.
- **range information** is surrounded by `@@` and shows the line numbers in old and new files.

NOTE: In most cases files are not renamed so both **old file** and **new file** will have the same name,
but will be in different states.

## Examples


#### File renamed no other changes

Example diff below shows a file being renamed from `Printer.php` to `Foo.php`. File contents remain unchanged.

```
diff --git a/src/Printer.php b/src/Foo.php
similarity index 100%
rename from src/Printer.php
rename to src/Foo.php
```

#### File deleted

Example diff where a file (in this case `User.php`) is deleted.

```
diff --git a/src/User.php b/src/User.php
deleted file mode 100644
index a86c17c..0000000
--- a/src/User.php
+++ /dev/null
@@ -1,49 +0,0 @@
-<?php
-
-declare(strict_types=1);
-
-
-namespace CodeDiff;
-
-
-class User
-{
-
-    /**
-     * @var string
-     */
-    private $name;
-
-    /**
-     * @var bool
-     */
-    private $isAdmin;
-
-    /**
-     * User constructor.
-     * @param string $name
-     * @param bool $isAdmin
-     */
-    public function __construct(string $name, bool $isAdmin)
-    {
-        $this->name = $name;
-        $this->isAdmin = $isAdmin;
-    }
-
-    /**
-     * @return string
-     */
-    public function getName(): string
-    {
-        return $this->name;
-    }
-
-    /**
-     * @return bool
-     */
-    public function isAdmin(): bool
-    {
-        return $this->isAdmin;
-    }
-
-}
```

#### File added

In this example the file `src/Person.php` has been added.

```
diff --git a/src/Person.php b/src/Person.php
new file mode 100644
index 0000000..81ee914
--- /dev/null
+++ b/src/Person.php
@@ -0,0 +1,34 @@
+<?php
+
+declare(strict_types=1);
+
+
+namespace CodeDiff;
+
+
+class Person
+{
+
+    /**
+     * @var string
+     */
+    private $name;
+
+    /**
+     * Person constructor.
+     * @param string $name
+     */
+    public function __construct(string $name)
+    {
+        $this->name = $name;
+    }
+
+    /**
+     * @return string
+     */
+    public function getName(): string
+    {
+        return $this->name;
+    }
+
+}
```


#### File changed

Change to the file `src/Person.php`

```
diff --git a/src/Person.php b/src/Person.php
index 81ee914..a7005b2 100644
--- a/src/Person.php
+++ b/src/Person.php
@@ -15,12 +15,18 @@ class Person
     private $name;

     /**
+     * @var bool
+     */
+    private $isAdmin;
+
+    /**
      * Person constructor.
      * @param string $name
      */
-    public function __construct(string $name)
+    public function __construct(string $name, bool $isAdmin)
     {
         $this->name = $name;
+        $this->isAdmin = $isAdmin;
     }

     /**
@@ -31,4 +37,12 @@ class Person
         return $this->name;
     }

+    /**
+     * @return bool
+     */
+    public function isAdmin(): bool
+    {
+        return $this->isAdmin;
+    }
+
 }
```


#### Binary file added

The file `images/icon.png` has been added.

```
diff --git a/images/icon.png b/images/icon.png
new file mode 100644
index 0000000..b5f60d7
Binary files /dev/null and b/images/icon.png differ
```

#### Binary file deleted

The file `images/icon.png` has been added.

```
diff --git a/images/icon.png b/images/icon.png
new file mode 100644
index 0000000..b5f60d7
Binary files /dev/null and b/images/icon.png differ
```



#### Binary file changed

The file `images/icon.png` has been altered.

```
diff --git a/images/icon.png b/images/icon.png
index 4e21d98..b5f60d7 100644
Binary files a/images/icon.png and b/images/icon.png differ
```


