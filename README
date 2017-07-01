== About ==
Orphaned talk pages adds a new Special page to MediaWiki: Special:OrphanedTalkPages. This special page lists all talk pages that do not have an accompanying page. The namespaces that are exempt from these checks is the user talk namespace by default, but this can be configured.

== Installation instructions ==
Note: you can also find these on https://www.mediawiki.org/wiki/Extension:OrphanedTalkPages

To use this extension, add:
wfLoadExtension( 'OrphanedTalkPages' );
to your LocalSettings.php file.

== Configuration instructions ==
This extension introduces two new configuration variables: $wgOrphanedTalkPagesExemptedNamespaces and $wgOrphanedTalkPagesIgnoreUserTalk.

* $wgOrphanedTalkPagesExemptedNamespaces controls which talk namespaces should be ignored when looking for orphaned talk pages. This variable is an array of namespace ids and is empty by default.
* $wgOrphanedTalkPagesIgnoreUserTalk determines if the user talk namespace (3, or NS_USER_TALK) should be ignored when looking for orphaned talk pages. This variable is a boolean and set to true by default.

== Other important notes ==
* The query that this special page uses is considered expensive. This means that when $wgMiserMode is enabled, the results will be served from cache.
* The special page may report talk pages as orphaned when they are not. The most common situation where this happens is File talk pages when $wgUseInstantCommons or $wgForeignFileRepos is enabled.
