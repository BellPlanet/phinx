<?php

namespace Test\Phinx\Util;

use Phinx\Util\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    private function getCorrectedPath($path)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function testGetExistingMigrationClassNames()
    {
        $expectedResults = [
            'TestMigration',
            'TestMigration2',
        ];

        $existingClassNames = Util::getExistingMigrationClassNames($this->getCorrectedPath(__DIR__ . '/_files/migrations'));
        $this->assertCount(count($expectedResults), $existingClassNames);
        foreach ($expectedResults as $expectedResult) {
            $this->assertContains($expectedResult, $existingClassNames);
        }
    }

    public function testGetExistingMigrationClassNamesWithFile()
    {
        $file = $this->getCorrectedPath(__DIR__ . '/_files/migrations/20120111235330_test_migration.php');
        $existingClassNames = Util::getExistingMigrationClassNames($file);
        $this->assertCount(0, $existingClassNames);
    }

    public function testGetCurrentTimestamp()
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $expected = $dt->format(Util::DATE_FORMAT);

        $current = Util::getCurrentTimestamp();

        // Rather than using a strict equals, we use greater/lessthan checks to
        // prevent false positives when the test hits the edge of a second.
        $this->assertGreaterThanOrEqual($expected, $current);
        // We limit the assertion time to 2 seconds, which should never fail.
        $this->assertLessThanOrEqual($expected + 2, $current);
    }

    public function testMapClassNameToFileName()
    {
        $expectedResults = [
            'CamelCase87afterSomeBooze' => '/^\d{14}_camel_case_87after_some_booze\.php$/',
            'CreateUserTable' => '/^\d{14}_create_user_table\.php$/',
            'LimitResourceNamesTo30Chars' => '/^\d{14}_limit_resource_names_to_30_chars\.php$/',
        ];

        foreach ($expectedResults as $input => $expectedResult) {
            $this->assertRegExp($expectedResult, Util::mapClassNameToFileName($input));
        }
    }

    public function testMapFileNameToClassName()
    {
        $expectedResults = [
            '20150902094024_create_user_table.php' => 'CreateUserTable',
            '20150902102548_my_first_migration2.php' => 'MyFirstMigration2',
            '20200412012035_camel_case_87after_some_booze.php' => 'CamelCase87afterSomeBooze',
            '20200412012036_limit_resource_names_to_30_chars.php' => 'LimitResourceNamesTo30Chars',
            '20200412012037_back_compat_names_to30_chars.php' => 'BackCompatNamesTo30Chars',
        ];

        foreach ($expectedResults as $input => $expectedResult) {
            $this->assertEquals($expectedResult, Util::mapFileNameToClassName($input));
        }
    }

    public function testisValidPhinxClassName()
    {
        $expectedResults = [
            'camelCase' => false,
            'CreateUserTable' => true,
            'UserSeeder' => true,
            'Test' => true,
            'test' => false,
            'Q' => true,
            'XMLTriggers' => true,
            'Form_Cards' => false,
            'snake_high_scores' => false,
            'Code2319Incidents' => true,
        ];

        foreach ($expectedResults as $input => $expectedResult) {
            $this->assertEquals($expectedResult, Util::isValidPhinxClassName($input));
        }
    }

    public function testGlobPath()
    {
        $files = Util::glob(__DIR__ . '/_files/migrations/empty.txt');
        $this->assertCount(1, $files);
        $this->assertEquals('empty.txt', basename($files[0]));

        $files = Util::glob(__DIR__ . '/_files/migrations/*.php');
        $this->assertCount(3, $files);
        $this->assertEquals('20120111235330_test_migration.php', basename($files[0]));
        $this->assertEquals('20120116183504_test_migration_2.php', basename($files[1]));
        $this->assertEquals('not_a_migration.php', basename($files[2]));
    }

    public function testGlobAll()
    {
        $files = Util::globAll([
            __DIR__ . '/_files/migrations/*.php',
            __DIR__ . '/_files/migrations/subdirectory/*.txt',
        ]);

        $this->assertCount(4, $files);
        $this->assertEquals('20120111235330_test_migration.php', basename($files[0]));
        $this->assertEquals('20120116183504_test_migration_2.php', basename($files[1]));
        $this->assertEquals('not_a_migration.php', basename($files[2]));
        $this->assertEquals('empty.txt', basename($files[3]));
    }

    public function testGetFiles()
    {
        $files = Util::getFiles([
            __DIR__ . '/_files/migrations',
            __DIR__ . '/_files/migrations/subdirectory',
            __DIR__ . '/_files/migrations/subdirectory',
        ]);

        $this->assertCount(4, $files);
        $this->assertEquals('20120111235330_test_migration.php', basename($files[0]));
        $this->assertEquals('20120116183504_test_migration_2.php', basename($files[1]));
        $this->assertEquals('not_a_migration.php', basename($files[2]));
        $this->assertEquals('foobar.php', basename($files[3]));
    }
}
