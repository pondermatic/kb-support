<?php
class KBS_UnitTestCase extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass() {
		kbs_install();
	}
}