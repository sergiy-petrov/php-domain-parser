<?php

declare(strict_types=1);

namespace Pdp\Storage;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use TypeError;
use function filter_var;
use function is_object;
use function is_string;
use function method_exists;
use const FILTER_VALIDATE_INT;

final class TimeToLive
{
    /**
     * Returns a DateInterval from string parsing.
     *
     * @param object|int|string $duration storage TTL object should implement the __toString method
     */
    public static function fromDurationString($duration): DateInterval
    {
        if (is_object($duration) && method_exists($duration, '__toString')) {
            $duration = (string) $duration;
        }

        if (false !== ($seconds = filter_var($duration, FILTER_VALIDATE_INT))) {
            if (-1 < $seconds) {
                return new DateInterval('PT'.$seconds.'S');
            }

            $interval = new DateInterval('PT'.($seconds * -1).'S');
            $interval->invert = 1;

            return $interval;
        }

        if (!is_string($duration)) {
            throw new TypeError('The ttl must null, an integer, a string, or a stringable object.');
        }

        /** @var DateInterval|false $date */
        $date = @DateInterval::createFromDateString($duration);
        if (!$date instanceof DateInterval) {
            throw new InvalidArgumentException(
                'The ttl value "'.$duration.'" can not be parsable by `DateInterval::createFromDateString`.'
            );
        }

        return $date;
    }

    /**
     * Returns a DateInterval relative to the current date and time.
     */
    public static function until(DateTimeInterface $date): DateInterval
    {
        return (new DateTimeImmutable('NOW', $date->getTimezone()))->diff($date, false);
    }

    /**
     * Returns a DateInterval relative to the current date and time.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 6.1.0 deprecated
     * @codeCoverageIgnore
     * @see TimeToLive::until
     */
    public static function fromDateTimeInterface(DateTimeInterface $date): DateInterval
    {
        return self::until($date);
    }

    /**
     * Returns a DateInterval from string parsing.
     *
     * @param object|int|string $duration storage TTL object should implement the __toString method
     *
     * @throws InvalidArgumentException if the value can not be parsable
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 6.1.0 deprecated
     * @codeCoverageIgnore
     * @see TimeToLive::fromDurationString
     */
    public static function fromScalar($duration): DateInterval
    {
        return self::fromDurationString($duration);
    }

    /**
     * Convert the input data into a DateInterval object or null.
     *
     * @param DateInterval|DateTimeInterface|object|int|string|null $ttl the object should implement the __toString method
     *
     * @throws InvalidArgumentException if the value can not be computed
     * @throws TypeError                if the value type is not recognized
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated 6.1.0 deprecated
     * @codeCoverageIgnore
     */
    public static function convert($ttl): ?DateInterval
    {
        if ($ttl instanceof DateInterval || null === $ttl) {
            return $ttl;
        }

        if ($ttl instanceof DateTimeInterface) {
            return self::until($ttl);
        }

        return self::fromDurationString($ttl);
    }
}
