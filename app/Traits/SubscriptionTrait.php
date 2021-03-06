<?php

namespace App\Traits;

use App\Models\Academy;
use App\Models\AcadSubscription;
use App\Models\Activity;
use App\Models\Coach;
use App\Models\Event;
use App\Models\Rate;
use App\Models\Subscription;
use App\Models\Time;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;

trait SubscriptionTrait
{

    //application memberships
    public function PreviousMemberShip(User $user)
    {
        return Subscription::with(['team' => function ($q) {
            $q->select('id', DB::raw('name_' . app()->getLocale() . ' as name'), 'photo');
        }])
            ->expired()
            ->where('user_id', $user->id)
            ->select('id', 'team_id', 'start_date', 'end_date')
            ->paginate(10);
    }

    public function CurrentMemberShip(User $user)
    {
        return Subscription::with(['team' => function ($q) {
            $q->select('id', DB::raw('name_' . app()->getLocale() . ' as name'), 'photo');
        }])
            ->current()
            ->where('user_id', $user->id)
            ->select('id', 'team_id', 'start_date', 'end_date')
            ->paginate(10);
    }


    public function allMemberShip(User $user)
    {
        return Subscription::with(['team' => function ($q) {
            $q->select('id', DB::raw('name_' . app()->getLocale() . ' as name'), 'photo');
        }])
            ->select('id', 'team_id', 'start_date', 'end_date')
            ->where('user_id', $user->id)
            ->orderBy('end_date', 'DESC')
            ->paginate(10);
    }

    //academy memberships

    public function PreviousAcademyMemberShip(User $user)
    {
        $attendance = "(SELECT count(id) FROM attendance WHERE attendance.subscription_id = academysubscriptions.id AND attendance.attend = 1 ) AS attendanceCount";

        return AcadSubscription::with(['team' => function ($q) {
            $q->select('id', 'coach_id', DB::raw('name_' . app()->getLocale() . ' as name'), 'photo', 'quotas');
        }])
            ->expired()
            ->where('user_id', $user->id)
            ->select('id', 'team_id', 'start_date', 'end_date', DB::raw($attendance))
            ->orderBy('end_date', 'DESC')
            ->paginate(10);
    }


    public function CurrentAcademyMemberShip(User $user)
    {
        return AcadSubscription::current()
            ->where('user_id', $user->id)
            ->select('id', 'team_id', 'start_date', 'end_date')
            ->orderBy('end_date', 'DESC')
            ->first();
    }


    public function academyMemberShipById($subscription_id)
    {
        return AcadSubscription::where('id', $subscription_id)
            ->select('id', 'team_id', 'start_date', 'end_date')
            ->orderBy('end_date', 'DESC')
            ->first();
    }


    public function getTeamTimes($teamId)
    {
        return $times = Time::where('team_id', $teamId)->pluck('day_name');
    }

    public function addUserAttendanceToEachDay($subscriptionDays, $userAttendanceDays)
    {

    }

    public function getRate($date, $coachId, $teamId, $userId, $rateable)
    {
        $rate = Rate::with(['coach' => function ($q) {
            $q->select('id', 'name_' . app()->getLocale() . ' as name', 'photo');
        }])
            ->where([
                ['coach_id', $coachId],
                ['team_id', $teamId],
                ['user_id', $userId],
                ['rateable', $rateable],
                ['date', $date],
            ])->select('id', 'comment', 'rate', 'coach_id')
            ->first();

        if ($rate)
            return $rate;
        else {
            $rate = new \stdClass();
            $rate->id = 0;
            $rate->comment = "";
            $rate->coach = Coach::select('id', 'name_' . app()->getLocale() . ' as name', 'photo')->find($coachId);
            $rate->rate = "";
            return $rate;
        }
    }

    public function checkIfDateRated($date, $coachId, $teamId, $userId, $rateable)
    {
        $rated = Rate::where([
            ['coach_id', $coachId],
            ['team_id', $teamId],
            ['user_id', $userId],
            ['rateable', $rateable],
            ['date', $date],
        ])
            ->first();

        if ($rated)
            return 1;
        else
            return 0;
    }

    public function currentRates($subscriptionId)
    {
        return Rate::with(['coach' => function ($qq) {
            $qq->select('id', 'name_' . app()->getLocale() . ' as name', 'photo');
        }])->select('id', 'rate', 'comment', 'coach_id', 'subscription_id', 'day_name', 'date')
            ->where('subscription_id', $subscriptionId)
            ->where('rateable', 1)
            ->paginate(10);
    }

    public function previousRates($previousSubscriptionsIds)
    {
        return Rate::with(['coach' => function ($qq) {
            $qq->select('id', 'name_' . app()->getLocale() . ' as name', 'photo');
        }])->select('id', 'rate', 'comment', 'coach_id', 'subscription_id', 'day_name', 'date')
            ->whereIn('subscription_id', $previousSubscriptionsIds)
            ->where('rateable', 1)
            ->paginate(10);
    }

    public function previousRatesBySubscriptionId($subscriptionId)
    {
        return Rate::with(['coach' => function ($qq) {
            $qq->select('id', 'name_' . app()->getLocale() . ' as name', 'photo');
        }])->select('id', 'rate', 'comment', 'coach_id', 'subscription_id', 'day_name', 'date')
            ->where('subscription_id', $subscriptionId)
            ->where('rateable', 1)
            ->paginate(10);
    }

}
