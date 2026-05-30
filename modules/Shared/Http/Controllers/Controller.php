<?php

namespace Modules\Shared\Http\Controllers;

/**
 * Base controller for the application's module controllers.
 *
 * In Laravel 11+ controllers no longer need a base class, so this is empty by
 * design — it lives here as a shared extension point: the place to add
 * cross-cutting controller behavior (authorization helpers, shared traits)
 * that every module's controllers should inherit. It sits in the Shared kernel
 * because it is depended on by multiple contexts and depends on nothing.
 */
abstract class Controller
{
    //
}
