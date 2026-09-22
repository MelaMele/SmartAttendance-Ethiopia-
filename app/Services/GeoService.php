<?php

namespace App\Services;

class GeoService
{
    /**
     * የሁለት ነጥቦችን ርቀት በሜትር ያሰላል (Haversine Formula)
     */
    public static function calculateDistanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // የምድር ራዲየስ በሜትር

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius); // ውጤት በሜትር
    }

    /**
     * ሰራተኛው ከተፈቀደው ራዲየስ (ለምሳሌ 100 ሜትር) ውስጥ መሆኑን ያረጋግጣል
     */
    public static function isWithinRadius(float $empLat, float $empLon, float $orgLat, float $orgLon, int $allowedRadius = 100): bool
    {
        $distance = self::calculateDistanceInMeters($empLat, $empLon, $orgLat, $orgLon);
        return $distance <= $allowedRadius;
    }
}
