<?php

namespace MediaWiki\Extension\OrphanedTalkPages\Specials;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\SpecialPage\PageQueryPage;
use Wikimedia\Rdbms\Subquery;

class SpecialOrphanedTalkPages extends PageQueryPage {
	private Config $config;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		parent::__construct( 'OrphanedTalkPages' );

		$this->config = $configFactory->makeConfig( 'OrphanedTalkPages' );
	}

	/** @inheritDoc */
	public function getPageHeader(): string {
		return $this->msg( 'orphanedtalkpages-text' )->parseAsBlock();
	}

	/**
	 * Overridden to prevent sorting by increasing values.
	 *
	 * @return false
	 */
	public function sortDescending(): bool {
		return false;
	}

	/** @inheritDoc */
	public function isExpensive(): bool {
		return true;
	}

	/** @inheritDoc */
	public function isSyndicated(): bool {
		return false;
	}

	/** @inheritDoc */
	public function getQueryInfo(): array {
		// $wgOrphanedTalkPagesExemptedNamespaces might be an integer.
		$exemptedNamespaces = (array)$this->config->get( 'OrphanedTalkPagesExemptedNamespaces' );

		// Check if the User talk namespace should be ignored
		if ( $this->config->get( 'OrphanedTalkPagesIgnoreUserTalk' ) ) {
			$exemptedNamespaces[] = NS_USER_TALK;
		}

		$dbr = $this->getDatabaseProvider()->getReplicaDatabase();
		$queryBuilder = $dbr->newSelectQueryBuilder()
			->select( [
				'namespace' => 'p1.page_namespace',
				'title' => 'p1.page_title',
				// Sorting
				'value' => 'p1.page_title'
			] )
			->from( 'page', 'p1' )
			->where( [
				'p1.page_title NOT' . $dbr->buildLike( $dbr->anyString(), '/', $dbr->anyString() ),
				'p1.page_namespace % 2 != 0'
			] );

		// Loop through the exempted namespaces
		foreach ( $exemptedNamespaces as $namespace ) {
			// Skip through non-integer values
			if ( !is_int( $namespace ) ) {
				continue;
			}
			$queryBuilder->andWhere( "p1.page_namespace != $namespace" );
		}

		$subQuery = $queryBuilder->newSubquery()
			->from( 'page', 'p2' )
			->field( '1' )
			->where( [
				'p2.page_namespace = p1.page_namespace - 1',
				'p1.page_title = p2.page_title'
			] )
			->caller( __METHOD__ )
			->getSQL();

		// Add the final condition
		$queryBuilder->andWhere( 'NOT EXISTS ' . new Subquery( $subQuery ) );

		return $queryBuilder->getQueryInfo();
	}

	/** @inheritDoc */
	protected function getGroupName(): string {
		return 'maintenance';
	}
}
