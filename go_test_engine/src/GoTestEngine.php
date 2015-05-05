<?php
final class GoTestEngine extends ArcanistUnitTestEngine {
  public function run() {
    $command = $this->getConfigurationManager()->getConfigFromAnySource('unit.engine.go.command');
    $future = new ExecFuture($command);

    do {
      list($stdout, $stderr) = $future->read();
      echo $stdout;
      echo $stderr;
      sleep(0.5);
    } while (!$future->isReady());

    list($error, $stdout, $stderr) = $future->resolve();

    $parser = new ArcanistGoTestResultParser();
    return $parser->parseTestResults("", $stdout);
  }
}
