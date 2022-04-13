# pdf-doc-indexer-custom
Modifications to commercial Joomla component PDF Doc Indexer by JoomDonation.

Enables PDF documents on web site file system to be processed and included in standard Joomla search results.
Improvements made for client site needs.

## Changes from published component
* Allows source documents to come from more than one directory; i.e., set source dir, index docs, change to another source dir, index those, etc.
* In order to do that, it stores the documents original source directory in the database so that the document can be linked to in search results.
* Obviously, the search plugin was modifed to use the original source directory in the search result links.

### Note
Avoid using "Reindex" feature as reindex will delete the full index and cause a bunch of re-work.

