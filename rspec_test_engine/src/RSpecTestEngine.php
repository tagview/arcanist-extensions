<?php

final class RSpecTestEngine extends ArcanistBaseUnitTestEngine {

  public function run() {
    $future = new ExecFuture('bundle exec rspec -f json');

    try {
      list($stdout, $stderr) = $future->resolvex();
      return $this->parseOutput($stdout);
    } catch(CommandException $execution) {
      return $this->parseOutput($execution->getStdout());
    }
  }

  public function shouldEchoTestResults() {
    return true;
  }

  private function parseOutput($output) {
    $results = array();

    $json = json_decode($output, true);

    foreach ($json['examples'] as $example) {
      $result = new ArcanistUnitTestResult();
      $result->setName($example['full_description']);
      $result->setDuration($example['run_time']);

      switch ($example['status']) {
        case 'passed':
          $result->setResult(ArcanistUnitTestResult::RESULT_PASS);
          break;

        case 'failed':
          $result->setResult(ArcanistUnitTestResult::RESULT_FAIL);
          $result->setUserData($example['exception']['message']);
          break;
      }

      $results[] = $result;
    }

    return $results;
  }
}
