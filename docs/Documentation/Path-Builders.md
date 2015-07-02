Path Builders
=============

Path builders are classes that are used to build the storage paths for a file based on the information coming from the `file_storage` table.

They implement at least these methods:

 * filename
 * path
 * fullPath
 * url
 
Each of them will take a `FileStorage` entity as first argument. Based on that entity it will generate a path depending on the logic implemented in the path builder.

The reason for this is to separate or share, just as needed, the path building logic between different storage systems.
