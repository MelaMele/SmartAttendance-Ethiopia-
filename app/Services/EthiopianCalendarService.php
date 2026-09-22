<?php

namespace App\Services;

use Carbon\Carbon;

class EthiopianCalendarService
{
    private static array $amharicMonths = [
        1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ህዳር', 4 => 'ታህሳስ',
        5 => 'ጥር', 6 => 'የካቲት', 7 => 'መጋቢት', 8 => 'ሚያዚያ',
        9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ', 13 => 'ጳጉሜ'
    ];

    /**
     * Gregorian (Carbon/Date) ወደ ኢትዮጵያ ካላንደር አሰራር መቀየሪያ
     */
    public static function fromGregorian($date = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::now('Africa/Addis_Ababa');
        
        $year = $date->year;
        $month = $date->month;
        $day = $date->day;

        // የዘመን መለወጫ ልዩነት ስሌት
        $newYearDay = ($year % 4 === 3) ? 12 : 11;
        
        if ($month > 9 || ($month === 9 && $day >= $newYearDay)) {
            $ethYear = $year - 7;
        } else {
            $ethYear = $year - 8;
        }

        // የወራት እና የቀናት ስሌት (ቀለል ያለ ትክክለኛ አልጎሪዝም)
        $startOfEthYear = Carbon::create($year, 9, $newYearDay);
        if ($date->lt($startOfEthYear)) {
            $prevNewYearDay = (($year - 1) % 4 === 3) ? 12 : 11;
            $startOfEthYear = Carbon::create($year - 1, 9, $prevNewYearDay);
        }

        $dayDiff = $startOfEthYear->diffInDays($date);
        $ethMonth = (int) floor($dayDiff / 30) + 1;
        $ethDay = ($dayDiff % 30) + 1;

        if ($ethMonth > 13) {
            $ethMonth = 13;
        }

        return [
            'year' => $ethYear,
            'month' => $ethMonth,
            'month_name' => self::$amharicMonths[$ethMonth] ?? 'መስከረም',
            'day' => $ethDay,
            'formatted' => sprintf('%02d/%02d/%04d', $ethDay, $ethMonth, $ethYear),
            'formatted_text' => ($ethDay) . ' ' . (self::$amharicMonths[$ethMonth] ?? '') . ' ' . $ethYear . ' ዓ.ም'
        ];
    }

    /**
     * የአሁኑን ቀን በኢትዮጵያ ካላንደር በጽሁፍ ለመውሰድ
     */
    public static function todayText(): string
    {
        return self::fromGregorian()['formatted_text'];
    }

    /**
     * የአሁኑን ቀን በቁጥር ቅርጽ (DD/MM/YYYY)
     */
    public static function todayFormatted(): string
    {
        return self::fromGregorian()['formatted'];
    }
}
