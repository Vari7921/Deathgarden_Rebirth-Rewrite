<?php

namespace App\Classes\Factory;

use App\Enums\Game\ChallengeType;
use App\Enums\Game\Faction;
use App\Enums\Game\RewardType;
use App\Exceptions\TimedChallengeFactoryException;
use App\Http\Responses\Api\General\Reward;
use App\Models\Game\TimedChallenge;
use Illuminate\Support\Carbon;

abstract class TimedChallengeFactory
{
    const AVAILABLE_DAILY_RUNNER = [
        '/Game/Challenges/Weekly/Challenge_BleedOut_RunnerWeekly.Challenge_BleedOut_RunnerWeekly' => 1,
        '/Game/Challenges/Daily/Challenge_Domination_Runner.Challenge_Domination_Runner' => 1,
        '/Game/Challenges/Weekly/Challenge_Emotional_RunnerWeekly.Challenge_Emotional_RunnerWeekly' => 3,
        '/Game/Challenges/Weekly/Challenge_Shields_RunnerWeekly.Challenge_Shields_RunnerWeekly' => 10
    ] ;

    const AVAILABLE_DAILY_HUNTER = [
        '/Game/Challenges/Daily/Challenge_Domination_Hunter.Challenge_Domination_Hunter' => 1,
        '/Game/Challenges/Weekly/Challenge_DroneActivation_HunterWeekly.Challenge_DroneActivation_HunterWeekly' => 5,
        '/Game/Challenges/Weekly/Challenge_Emotional_HunterWeekly.Challenge_Emotional_HunterWeekly' => 3,
        '/Game/Challenges/Weekly/Challenge_Headshot_HunterWeekly.Challenge_Headshot_HunterWeekly' => 1,
        '/Game/Challenges/Weekly/Challenge_InDenial_HunterWeekly.Challenge_InDenial_HunterWeekly' => 3,
    ];

    const AVAILABLE_WEEKLY_RUNNER = [
        '/Game/Challenges/Weekly/Challenge_BleedOut_RunnerWeekly.Challenge_BleedOut_RunnerWeekly' => 5,
        '/Game/Challenges/Weekly/Challenge_Emotional_RunnerWeekly.Challenge_Emotional_RunnerWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_Greed_RunnerWeekly.Challenge_Greed_RunnerWeekly' => 50,
        '/Game/Challenges/Weekly/Challenge_Shields_RunnerWeekly.Challenge_Shields_RunnerWeekly' => 100,
        '/Game/Challenges/Weekly/Challenge_SpeedCapture_RunnerWeekly.Challenge_SpeedCapture_RunnerWeekly' => 15,
        '/Game/Challenges/Weekly/Challenge_UPs_RunnerWeekly.Challenge_UPs_RunnerWeekly' => 5,
        '/Game/Challenges/Weekly/Challenge_Wasteful_RunnerWeekly.Challenge_Wasteful_RunnerWeekly' => 100
    ];

    const AVAILABLE_WEEKLY_HUNTER = [
        '/Game/Challenges/Weekly/Challenge_ARB_Damage_HunterWeekly.Challenge_ARB_Damage_HunterWeekly' => 5000,
        '/Game/Challenges/Weekly/Challenge_AssaultRifleWins_HunterWeekly.Challenge_AssaultRifleWins_HunterWeekly' => 100,
        '/Game/Challenges/Weekly/Challenge_Damage_HunterWeekly.Challenge_Damage_HunterWeekly' => 5000,
        '/Game/Challenges/Weekly/Challenge_DroneActivation_HunterWeekly.Challenge_DroneActivation_HunterWeekly' => 25,
        '/Game/Challenges/Weekly/Challenge_Emotional_HunterWeekly.Challenge_Emotional_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_Greed_HunterWeekly.Challenge_Greed_HunterWeekly' => 50,
        '/Game/Challenges/Weekly/Challenge_Headshot_HunterWeekly.Challenge_Headshot_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_HuntingShotgunWins_HunterWeekly.Challenge_HuntingShotgunWins_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_InDenial_HunterWeekly.Challenge_InDenial_HunterWeekly' => 20,
        '/Game/Challenges/Weekly/Challenge_LMGWins_HunterWeekly.Challenge_LMGWins_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_Reveals_hunterWeekly.Challenge_Reveals_hunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_RingOut_hunterWeekly.Challenge_RingOut_hunterWeekly' => 5,
        '/Game/Challenges/Weekly/Challenge_Mines_HunterWeekly.Challenge_Mines_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_ShotgunDowns_HunterWeekly.Challenge_ShotgunDowns_HunterWeekly' => 10,
        '/Game/Challenges/Weekly/Challenge_Wasteful_HunterWeekly.Challenge_Wasteful_HunterWeekly' => 100,
    ];

    const DAILY_REWARDS = [
        'CurrencyA' => [
            'min' => 100,
            'max' => 250,
        ],
        'CurrencyB' => [
            'min' => 100,
            'max' => 250,
        ],
        'CurrencyC' => [
            'min' => 250,
            'max' => 500,
        ],
    ];

    const WEEKLY_REWARDS = [
        'CurrencyA' => [
            'min' => 1000,
            'max' => 1500,
        ],
        'CurrencyB' => [
            'min' => 1000,
            'max' => 1500,
        ],
        'CurrencyC' => [
            'min' => 1500,
            'max' => 2000,
        ],
    ];

    /**
     * Makes a new TimedChallenge Instance that's not yet saved to the Database.
     *
     * @param Carbon $startTime
     * @param Faction $faction
     * @param ChallengeType $type
     * @return void
     * @throws TimedChallengeFactoryException
     */
    public static function makeChallenge(
        Carbon $startTime,
        Faction $faction,
        ChallengeType $type,
    ): TimedChallenge
    {
        [$blueprintPath, $completionValue] = static::pickChallenge($faction, $type);

        $challenge = new TimedChallenge();
        $challenge->type = $type;
        $challenge->faction = $faction;
        $challenge->blueprint_path = $blueprintPath;
        $challenge->completion_value = $completionValue;

        $challenge->start_time = $startTime;

        if($type === ChallengeType::Daily)
            $challenge->end_time = $startTime->copy()->addDay();
        else
            $challenge->end_time = $startTime->copy()->addWeek();

        $challenge->rewards = static::pickReward($type);

        return $challenge;
    }

    protected static function pickChallenge(
        Faction $faction,
        ChallengeType $type,
    ): array {
        $challengeArray = match ($faction) {
            Faction::Hunter => static::AVAILABLE_DAILY_HUNTER,
            Faction::Runner => static::AVAILABLE_DAILY_RUNNER,
            default => null,
        };

        if($challengeArray === null)
            throw new TimedChallengeFactoryException('Unallowed Faction ('. $faction->value .'), could not select a challenge.');

        $blueprintPath = array_rand($challengeArray);
        $completionValue = $challengeArray[$blueprintPath];

        return [$blueprintPath, $completionValue];
    }

    /**
     * @param ChallengeType $type
     * @return Reward
     * @throws TimedChallengeFactoryException
     */
    protected static function pickReward(
        ChallengeType $type,
    ): Reward {
        $rewardsArray = match ($type) {
            ChallengeType::Daily => static::DAILY_REWARDS,
            ChallengeType::Weekly => static::WEEKLY_REWARDS,
            default => null,
        };

        if($rewardsArray === null)
            throw new TimedChallengeFactoryException('Unallowed Challenge Type (' . $type->value . '), could not select a reward.');

        $pickedReward = array_rand($rewardsArray);

        return new Reward(
            RewardType::Currency,
            round(rand($rewardsArray[$pickedReward]['min'], $rewardsArray[$pickedReward]['max']) / 10) * 10,
            $pickedReward,
        );
    }
}