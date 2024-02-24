<?php

namespace App\Classes\Character;

use Illuminate\Support\Arr;

abstract class CharacterItemConfig
{
    const RUNNER_EQUIPPED_PERK_COUNT = 2;

    const RUNNER_EQUIPPED_WEAPON_COUNT = 1;

    const HUNTER_EQUIPPED_PERK_COUNT = 2;

    const HUNTER_EQUIPPED_WEAPON_COUNT = 2;

    protected static array $defaultEquippedPerks;

    protected static array $defaultEquippedWeapons;

    protected static array $defaultEquipment;

    protected static array $defaultEquippedBonuses;

    protected static array $defaultEquippedPowers = [];

    /**
     * additional perks for Runners and Equipped Powers for Hunters
     *
     * @var array
     */
    protected static array $additionalPerks;

    protected static array $additionalWeapons;

    public static function getDefaultEquippedPerks(): array {
        return static::$defaultEquippedPerks;
    }

    public static function getDefaultEquippedWeapons(): array {
        return static::$defaultEquippedWeapons;
    }

    public static function getDefaultEquipment(): array {
        return static::$defaultEquipment;
    }

    public static function getDefaultEquippedBonuses(): array {
        return static::$defaultEquippedBonuses;
    }

    public static function getDefaultPowers()
    {
        return static::$defaultEquippedPowers;
    }

    public static function getAllowedPerks(): array {
        return [...static::$additionalPerks, ...static::$defaultEquippedPerks];
    }

    public static function getAllowedWeapons(): array {
        return [...static::$additionalWeapons, ...static::$defaultEquippedWeapons];
    }
}