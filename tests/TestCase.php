<?php

namespace Tests;

use App\Models\DoctorProfile;
use App\Models\Setting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Both of these memoise on a static property, which is right for a request
     * and wrong for a test run: the property outlives the application, while
     * RefreshDatabase empties the table underneath it and the array cache store
     * is rebuilt. Left alone, one test's settings decide the next test's
     * feature switches.
     *
     * Individual tests still call forgetCache() after writing a row mid-test.
     * This is only about what one test hands to the next.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Setting::forgetCache();
        DoctorProfile::forgetCache();
    }
}
