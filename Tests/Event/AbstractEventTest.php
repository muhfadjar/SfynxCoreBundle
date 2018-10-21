<?php
/**
 * This file is part of the <Core> project.
 *
 * @category   Core
 * @package    Test
 * @subpackage Event
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since      2015-01-08
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Event;

use Phake;
use \PHPUnit\Framework\TestCase;

/**
 * @category   Core
 * @package    Test
 * @subpackage Event
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
abstract class AbstractEventTest extends TestCase
{
    protected function createEvent($subject)
    {
        $event = Phake::mock('Symfony\Component\EventDispatcher\GenericEvent');
        Phake::when($event)->getSubject()
            ->thenReturn($subject);

        return $event;
    }
}
