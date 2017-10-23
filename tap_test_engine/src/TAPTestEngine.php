<?php

final class TAPTestEngine extends ArcanistUnitTestEngine {

  public function run() {
    $projectRoot = $this->getWorkingCopy()->getProjectRoot();
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.tap.command');
    $eol = $this->getConfigurationManager()->getConfigFromAnySource(
      'unit.engine.tap.eol'
    );

    /**
     * Specifying line endings via config is optional, so default to PHP_EOL if not specified.
     */
    if (!$eol) {
      $eol = PHP_EOL;
    }

    $future = new ExecFuture($command);
    $future->setCWD(Filesystem::resolvePath($projectRoot));

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;
      sleep(0.5);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();
    return $this->parseOutput($stdout, $eol);
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output, $eol) {
    $results = array();
    $lines = explode($eol, $output);

    foreach($lines as $index => $line) {
      preg_match('/^(not ok|ok)\s+\d+\s+-?(.*)/', $line, $matches);
      if (count($matches) < 3) continue;

      $result = new ArcanistUnitTestResult();
      $result->setName(trim($matches[2]));

      switch (trim($matches[1])) {
        case 'ok':
          $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
          break;

        case 'not ok':
          $exception_message = trim($lines[$index + 1]);
          $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
          $result->setUserData($exception_message);
          break;

        default:
          continue;
      }

      $results[] = $result;
    }

    return $results;
  }
}
