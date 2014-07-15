<?php

final class MultiTestEngine extends ArcanistBaseUnitTestEngine {

  public function run() {
    $engines = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.multi-test.engines');

    $results = array();

    foreach ($engines as $engine_class) {
      $engine = $this->instantiateEngine($engine_class);
      $results = array_merge($results, $engine->run());
    }

    return $results;
  }

  private function instantiateEngine($engine_class) {
    if (!class_exists($engine_class) || !is_subclass_of($engine_class, 'ArcanistBaseUnitTestEngine')) {
      throw new ArcanistUsageException(
        "Configured unit test engine '{$engine_class}' is not a subclass of 'ArcanistBaseUnitTestEngine'."
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
