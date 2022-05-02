<?php

/*
 * Author Thomas Beauchataud
 * Since 18/03/2022
 */

namespace TBCD\MessengerExtension\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * A stamp to mark messages ran by the DependenciesMiddleware to prevent a cascade call
 *
 * @author Thomas Beauchataud
 * @since 02/05/2022
 */
class DependencyStamp implements StampInterface
{

}