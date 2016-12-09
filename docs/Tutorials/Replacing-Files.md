Replacing Files
===============

**Don't** use Table::deleteAll() if you don't want to end up with orphaned files! The reason for that is that deleteAll() doesn't fire the callbacks. So the events that will remove the files won't get fired.
