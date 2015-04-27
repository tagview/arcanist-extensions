<?php

final class MultiTestEngine extends ArcanistUnitTestEngine {

  public function run() {
    $config = $this->getConfigurationManager();

    $engines = $config->getConfigFromAnySource('unit.engine.multi-test.engines');

    $results = array();

    foreach ($engines as $engine_or_configuration) {
      $include = false;
      $includeReport = false;
      $changedFileMatchesInclude = false;

      if (is_array($engine_or_configuration)) {
        $engine_class = $engine_or_configuration['engine'];
        foreach ($engine_or_configuration as $configuration => $value) {
          if ($configuration != 'engine' &&
              $configuration != 'include' &&
              $configuration != 'include_report') {
            $config->setRuntimeConfig($configuration, $value);
          }

          switch($configuration) {
            case 'include':
              $include = $value;
              break;
            case 'include_report':
              $includeReport = $value;
              break;
          }
        }
      } else {
        $engine_class = $engine_or_configuration;
      }

      $engine = $this->instantiateEngine($engine_class);

      // If a regex is configured we'll do a match on all changed files to see if there's a match
      // before calling $engine->run()
      if ($include) {
        $include = '/' . $include . '/';
        foreach ($this->getPaths() as $path) {
          if (preg_match($include, $path)) {
            $changedFileMatchesInclude = true;
            break;
          }
        }
      }

      // No need to execute the unit tests, so bail - but do report that we skipped running tests.
      if (!$changedFileMatchesInclude) {
        $test_results = [];
        // Be silent by default if no files match the include regex, but if the config
        // includes a bit to see it, output a skip test result for visibility when
        // no tests match the include regex.
        if ($includeReport) {
          $skip_test = new ArcanistUnitTestResult();
          $skip_test->setName("No changed files match " . $include . " - not running " . $engine_class);
          $skip_test->setResult(ArcanistUnitTestResult::RESULT_SKIP);
          $test_results[] = $skip_test;
        }

      // Actually run the tests if we didn't already decide not to run them.
      } else {
        $test_results = $engine->run();
      }

      $results = array_merge($results, $test_results);
    }

    return $results;
  }

  private function instantiateEngine($engine_class) {
    $is_test_engine = is_subclass_of($engine_class, 'ArcanistBaseUnitTestEngine') || is_subclass_of($engine_class, 'ArcanistUnitTestEngine');

    if (!class_exists($engine_class) || !$is_test_engine) {
      throw new ArcanistUsageException(
        "Configured unit test engine '{$engine_class}' is not a subclass of 'ArcanistUnitTestEngine'."
      );
    }

    $engine = newv($engine_class, array());
    $engine->setWorkingCopy($this->getWorkingCopy());
    $engine->setConfigurationManager($this->getConfigurationManager());
    $engine->setRunAllTests($this->getRunAllTests());
    $engine->setPaths($this->getPaths());

    return $engine;
  }
}
