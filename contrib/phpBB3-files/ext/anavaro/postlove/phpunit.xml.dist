<?xml version="1.0" encoding="UTF-8"?>

<phpunit backupGlobals="true"
         backupStaticAttributes="true"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false"
         verbose="true"
         bootstrap="../../../../tests/bootstrap.php"
>
	<testsuites>
		<testsuite name="Extension Test Suite">
			<directory suffix="_test.php">./tests</directory>
			<exclude>./tests/functional</exclude>
		</testsuite>
		<testsuite name="Extension Functional Tests">
			<directory suffix="_test.php" phpVersion="5.3.19" phpVersionOperator=">=">./tests/functional/</directory>
		</testsuite>
	</testsuites>

	<filter>
		<blacklist>
			<directory>./tests/</directory>
		</blacklist>
		<whitelist processUncoveredFilesFromWhitelist="true">
			<directory suffix=".php">./</directory>
			<exclude>
				<directory suffix=".php">./language/</directory>
				<directory suffix=".php">./migrations/</directory>
				<directory suffix=".php">./tests/</directory>
			</exclude>
		</whitelist>
	</filter>
</phpunit>
