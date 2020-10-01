<?php

namespace MediaWiki\Extensions\OrphanedTalkPages;

use MediaWiki\Extensions\OrphanedTalkPages\Specials\SpecialOrphanedTalkPages;
use MediaWiki\SpecialPage\Hook\WgQueryPagesHook;

class OrphanedTalkPagesHooks implements WgQueryPagesHook {
	/**
	 * Hook to add Special:OrphanedTalkPages to the list generated by QueryPage::getPages.
	 * Used by the maintenance script updateSpecialPages.
	 *
	 * @inheritDoc
	 *
	 * @param array &$qp
	 */
	public function onwgQueryPages( &$qp ) : void {
		$qp[] = [ SpecialOrphanedTalkPages::class, 'OrphanedTalkPages' ];
	}
}
