<?php

/**
 * This file is part of Gush.
 *
 * (c) Luis Cordova <cordoval@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gush\Tests\Command;

use Gush\Command\PullRequestSquashCommand;
use Gush\Tests\Fixtures\OutputFixtures;

/**
 * @author Luis Cordova <cordoval@gmail.com>
 */
class PullRequestSquashCommandTest extends BaseTestCase
{
    public function testCommand()
    {
        $this->expectConfig();
        $processHelper = $this->expectProcessHelper();
        $tester = $this->getCommandTester($command = new PullRequestSquashCommand());
        $command->getHelperSet()->set($processHelper, 'process');
        $tester->execute(['--org' => 'cordoval', 'pr_number' => 40], ['interactive' => false]);

        $this->assertEquals(OutputFixtures::PULL_REQUEST_SQUASH, trim($tester->getDisplay()));
    }

    private function expectProcessHelper()
    {
        $processHelper = $this->getMock(
            'Gush\Helper\ProcessHelper',
            ['runCommands']
        );
        $processHelper->expects($this->once())
            ->method('runCommands')
            ->with([
                [
                    'line' => 'git remote update',
                    'allow_failures' => true
                ],
                [
                    'line' => 'git checkout head_ref',
                    'allow_failures' => true
                ],
                [
                    'line' => 'git reset --soft base_ref',
                    'allow_failures' => true
                ],
                [
                    'line' => 'git commit -am head_ref',
                    'allow_failures' => true
                ],
                [
                    'line' => sprintf('git push -u cordoval head_ref -f'),
                    'allow_failures' => true
                ],
            ])
        ;

        return $processHelper;
    }

    private function expectConfig()
    {
        $this
            ->config
            ->expects($this->once())
            ->method('get')
            ->with('authentication')
            ->will($this->returnValue(['username' => 'cordoval']))
        ;
    }
}
