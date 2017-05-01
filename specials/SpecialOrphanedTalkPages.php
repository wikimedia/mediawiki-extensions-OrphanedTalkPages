<?php

/**
 * Created by PhpStorm.
 * User: kswer
 * Date: 26-2-2017
 * Time: 19:37
 */
class SpecialOrphanedTalkPages extends PageQueryPage {
	function __construct( $name = 'OrphanedTalkPages' ) {
		parent::__construct( $name );
	}

	function getPageHeader() {
		return $this->msg( 'orphanedtalkpages-text' )->parseAsBlock();
	}

	function sortDescending() {
		return false;
	}

	public function isExpensive() {
		return true;
	}

	function isSyndicated() {
		return false;
	}

	public function getQueryInfo() {
		global $wgOrphanedTalkPagesExemptedNamespaces, $wgOrphanedTalkPagesIgnoreUserTalk;

		// Check if the configuration global is not empty and an array
		if ( empty( $wgOrphanedTalkPagesExemptedNamespaces ) ||
		     !is_array( $wgOrphanedTalkPagesExemptedNamespaces ) ) {

			// Check if it is an int if it isn't an array so single values still work
			if ( is_int( $wgOrphanedTalkPagesExemptedNamespaces ) ) {
				$wgOrphanedTalkPagesExemptedNamespaces = [
					$wgOrphanedTalkPagesExemptedNamespaces
				];
			} else {
				$wgOrphanedTalkPagesExemptedNamespaces = [];
			}
		}

		// Check if the User talk namespace should be ignored.
		if ( $wgOrphanedTalkPagesIgnoreUserTalk === true ) {
			$wgOrphanedTalkPagesExemptedNamespaces[] = NS_USER_TALK;
		}

		$query = [
			'tables' => 'page AS p1',
			'fields' => [
				'namespace' => 'p1.page_namespace',
				'title' =>'p1.page_title',
			    'value' => 'page_title' // Sorting
			],
			'conds' => [
				'p1.page_title NOT LIKE "%/%"',
			    'p1.page_namespace % 2 != 0'
			]
		];

		// Check if the configuration variable is still empty
		if ( !empty( $wgOrphanedTalkPagesExemptedNamespaces ) ) {
			// Loop through the exempted namespaces
			foreach ( $wgOrphanedTalkPagesExemptedNamespaces as $namespace ) {
				// Skip through non-numeric values
				if ( !is_numeric( $namespace ) ) {
					continue;
				}
				$query['conds'][] = "p1.page_namespace != $namespace";
			}
		}

		// Add the final condition
		$query['conds'][] = 'NOT EXISTS (SELECT 1 FROM page AS p2 WHERE p2.page_namespace = p1.page_namespace - 1 AND p1.page_title = p2.page_title)';

		return $query;
	}

	protected function getGroupName() {
		return 'maintenance';
	}
}
