<?php

namespace Tests\Controllers;

/**
 * HomeTestController
 *
 * Test controller for validating the Routing module.
 * Simulates standard controller actions mapped to HTTP routes for automated functional tests.
 * All methods return descriptive strings for easy result verification.
 */
class HomeTestController
{
    /**
     * Handles the GET / route.
     *
     * @param mixed $request [Optional] The request object, if provided by dispatcher.
     * @return string
     */
    public function handle($request = null): string
    {
        return '[HomeTestController] handle() called: route /';
    }

    /**
     * Handles the GET /home route.
     *
     * @param mixed $request [Optional] The request object, if provided by dispatcher.
     * @return string
     */
    public function handleHome($request = null): string
    {
        return '[HomeTestController] handleHome() called: route /home';
    }

    /**
     * Handles the GET /quotationManager route.
     *
     * @param mixed $request [Optional] The request object, if provided by dispatcher.
     * @return string
     */
    public function handleQuotationManager($request = null): string
    {
        return '[HomeTestController] handleQuotationManager() called: route /quotationManager';
    }

    /**
     * Handles the GET /custom dynamic route.
     *
     * @param mixed $request [Optional] The request object, if provided by dispatcher.
     * @return string
     */
    public function handleCustom($request = null): string
    {
        return '[HomeTestController] handleCustom() called: route /custom';
    }
}
