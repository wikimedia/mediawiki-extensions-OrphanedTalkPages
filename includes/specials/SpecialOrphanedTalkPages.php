<?php

namespace MediaWiki\Extension\OrphanedTalkPages\Specials;

use Config;
use ConfigFactory;
use PageQueryPage;

class SpecialOrphanedTalkPages extends PageQueryPage {
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		parent::__construct( 'OrphanedTalkPages' );

		$this->config = $configFactory->makeConfig( 'OrphanedTalkPages' );
	}

	/**
	 * The content returned by this function is output before any result.
	 *
	 * @return string
	 */
	public function getPageHeader() {
		return $this->msg( 'orphanedtalkpages-text' )->parseAsBlock();
	}

	/**
	 * Overridden to prevent sorting by increasing values.
	 *
	 * @return bool
	 */
	public function sortDescending() {
		return false;
	}

	/**
	 * Is this query expensive? Then we
	 * don't let it run in miser mode. $wgDisableQueryPages causes all query
	 * pages to be declared expensive. Some query pages are always expensive.
	 *
	 * @return bool
	 */
	public function isExpensive() {
		return true;
	}

	/**
	 * Sometime we don't want to build rss / atom feeds.
	 *
	 * @return bool
	 */
	public function isSyndicated() {
		return false;
	}

	/**
	 * Subclasses return an SQL query here, formatted as an array with the
	 * following keys:
	 *    tables => Table(s) for passing to Database::select()
	 *    fields => Field(s) for passing to Database::select(), may be *
	 *    conds => WHERE conditions
	 *    options => options
	 *    join_conds => JOIN conditions
	 *
	 * Note that the query itself should return the following three columns:
	 * 'namespace', 'title', and 'value'. 'value' is used for sorting.
	 *
	 * These may be stored in the querycache table for expensive queries,
	 * and that cached data will be returned sometimes, so the presence of
	 * extra fields can't be relied upon. The cached 'value' column will be
	 * an integer; non-numeric values are useful only for sorting the
	 * initial query (except if they're timestamps, see usesTimestamps()).
	 *
	 * Don't include an ORDER or LIMIT clause, they will be added.
	 *
	 * If this function is not overridden or returns something other than
	 * an array, getSQL() will be used instead. This is for backwards
	 * compatibility only and is strongly deprecated.
	 * @return array
	 */
	public function getQueryInfo(): array {
		// $wgOrphanedTalkPagesExemptedNamespaces might be an integer.
		$exemptedNamespaces = (array)$this->config->get( 'OrphanedTalkPagesExemptedNamespaces' );

		// Check if the User talk namespace should be ignored
		if ( $this->config->get( 'OrphanedTalkPagesIgnoreUserTalk' ) ) {
			$exemptedNamespaces[] = NS_USER_TALK;
		}

		$query = [
			'tables' => [
				'p1' => 'page'
			],
			'fields' => [
				'namespace' => 'p1.page_namespace',
				'title' => 'p1.page_title',
				// Sorting
				'value' => 'p1.page_title'
			],
			'conds' => [
				'p1.page_title NOT LIKE "%/%"',
				'p1.page_namespace % 2 != 0'
			]
		];

		// Loop through the exempted namespaces
		foreach ( $exemptedNamespaces as $namespace ) {
			// Skip through non-integer values
			if ( !is_int( $namespace ) ) {
				continue;
			}
			$query['conds'][] = "p1.page_namespace != $namespace";
		}

		$subQuery = $this->getRecacheDB()->selectSQLText(
			[ 'p2' => 'page' ],
			'1',
			[
				'p2.page_namespace = p1.page_namespace - 1',
				'p1.page_title = p2.page_title'
			]
		);

		// Add the final condition
		$query['conds'][] = "NOT EXISTS ($subQuery)";

		return $query;
	}

	/**
	 * Under which header this special page is listed in Special:SpecialPages.
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'maintenance';
	}
}
