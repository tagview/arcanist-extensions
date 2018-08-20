<?php

final class ArcanistEslintLinter extends ArcanistLinter {
  private $execution;

  public function getInfoName() {
    return 'Eslint';
  }

  public function getInfoURI() {
    return 'http://eslint.org/';
  }

  public function getInfoDescription() {
    return pht('The pluggable linting utility for JavaScript and JSX');
  }

  public function getLinterName() {
    return 'Eslint';
  }

  public function getLinterConfigurationName() {
    return 'eslint';
  }

  public function getLinterConfigurationOptions() {
    return array();
  }

  final public function lintPath($path) {}

  public function willLintPaths(array $paths) {
    if(!$this->checkLocalEslintInstallation() && !$this->checkGlobalEslintInstallation()){
      throw new ArcanistUsageException(
        pht('Eslint is not installed, please run `npm install eslint` or add it to your package.json')
      );
    }

    $this->setExecution($paths);
    $this->didRunLinters();
  }

  final public function didRunLinters() {
    if ($this->execution) {
      list($err, $stdout, $stderr) = $this->execution->resolve();

      $messages = $this->parseLinterOutput($stdout);

      foreach ($messages as $message) {
        $this->addLintMessage($message);
      }
    }
  }

  private function checkGlobalEslintInstallation() {
    if (Filesystem::binaryExists('eslint')) {
      return true;
    }
    return false;
  }

  private function checkLocalEslintInstallation(){
    if (Filesystem::binaryExists('.\node_modules\.bin\eslint')) {
      return true;
    }
    return false;
  }

  private function setExecution(array $paths){
    if($this->checkLocalEslintInstallation()){
      $this->execution = new ExecFuture('.\node_modules\.bin\eslint --format=json --no-color ' . implode($paths, ' '));
    }
    else{
      $this->execution = new ExecFuture('eslint --format=json --no-color ' . implode($paths, ' '));
    }
  }
    
  protected function parseLinterOutput($output) {
    $json = json_decode($output, true);

    $severityMap = array();
    $severityMap['0'] = 'warning';
    $severityMap['1'] = 'warning';
    $severityMap['2'] = 'error';

    $messages = array();

    foreach ($json as $file) {
      foreach ($file['messages'] as $offense) {
        $message = new ArcanistLintMessage();
        $message->setPath($file['filePath']);
        $message->setLine($offense['line']);
        $message->setChar($offense['column']);
        $message->setCode($offense['source']);
        $message->setName($offense['ruleId']);
        $message->setDescription($offense['message']);
        $message->setseverity($severityMap[$offense['severity']]);

        $messages[] = $message;
      }
    }

    return $messages;
  }

}
