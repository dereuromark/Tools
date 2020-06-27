<?php

namespace TestApp\Model\Entity;

use Tools\Model\Entity\Entity;

class BitmaskedComment extends Entity {

	/**
	 * @param mixed|null $value
	 * @return mixed
	 */
	public static function statuses($value = null) {
		$options = [
			static::STATUS_ACTIVE => __d('tools', 'Active'),
			static::STATUS_PUBLISHED => __d('tools', 'Published'),
			static::STATUS_APPROVED => __d('tools', 'Approved'),
			static::STATUS_FLAGGED => __d('tools', 'Flagged'),
		];

		return parent::enum($value, $options);
	}

	public const STATUS_NONE = 0;
	public const STATUS_ACTIVE = 1;
	public const STATUS_PUBLISHED = 2;
	public const STATUS_APPROVED = 4;
	public const STATUS_FLAGGED = 8;

}
