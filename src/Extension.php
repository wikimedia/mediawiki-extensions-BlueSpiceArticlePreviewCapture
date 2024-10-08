<?php
/**
 * Extension class for BlueSpiceArticlePrewiewCapture extension for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpice_pro
 * @subpackage BlueSpiceArticlePrewiewCapture
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

namespace BlueSpice\ArticlePreviewCapture;

class Extension extends \BlueSpice\Extension {

	/**
	 * @return void
	 */
	public static function callback() {
		// Register APC file repo
		$GLOBALS['wgForeignFileRepos'][] = [
			'class' => \FileRepo::class,
			'backend' => 'FileRepo',
			'name' => 'ArticlePreviewCapture',
			'directory' => BS_DATA_DIR . '/ArticlePreviewCapture/',
			'hashLevels' => 0,
			'url' => BS_DATA_PATH . '/ArticlePreviewCapture',
			'scriptDirUrl' => $GLOBALS['wgScriptPath'],
		];
	}
}
