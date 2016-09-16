<?php

final class ArcanistEslintLinter extends ArcanistLinter {
  private $execution;
  private $eslintBinaryPath = null;

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
    $options = array(
      'eslint-binary-path' => array(
        'type' => 'optional string',
        'help' => pht('Path to the eslint binary to use.  If not, specified uses the global one.'),
      ),
    );
    return $options + parent::getLinterConfigurationOptions();
  }

  public function setLinterConfigurationValue($key, $value) {
    switch ($key) {
      case 'eslint-binary-path':
        $this->eslintBinaryPath = $value;
        return;
    }
    return parent::setLinterConfigurationValue($key, $value);
  }

  final public function lintPath($path) {}

  public function willLintPaths(array $paths) {
    $this->checkEslintInstallation();
    $eslintBinary = $this->eslintBinaryPath ?: 'eslint';
    $this->execution = new ExecFuture("$eslintBinary --format=json --no-color " . implode($paths, ' '));
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

  private function checkEslintInstallation() {
    if ($this->eslintBinaryPath && !Filesystem::binaryExists($this->eslintBinaryPath)) {
      throw new ArcanistUsageException(
        pht('A eslint binary was specified but it cannot be found.  To use the global eslint do not specify a `eslint-binary-path`')
      );
    } else if (!$this->eslintBinaryPath && !Filesystem::binaryExists('eslint')) {
      throw new ArcanistUsageException(
        pht('Eslint is not installed, please run `npm install -g eslint` or set the `eslint-binary-path` option in your .arclint')
      );
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
