<?php

final class ArcanistRubocopLinter extends ArcanistLinter {
  private $execution;

  public function getInfoName() {
    return 'Rubocop';
  }

  public function getInfoURI() {
    return 'https://github.com/bbatsov/rubocop';
  }

  public function getInfoDescription() {
    return pht('RuboCop is a Ruby static code analyzer. Out of the '.
      'box it will enforce many of the guidelines outlined in the community '.
      'Ruby Style Guide.');
  }

  public function getLinterName() {
    return 'Rubocop';
  }

  public function getLinterConfigurationName() {
    return 'rubocop';
  }

  public function getLinterConfigurationOptions() {
    return array();
  }

  final public function lintPath($path) {}

  public function willLintPaths(array $paths) {
    $this->checkRubocopInstallation();
    $this->execution = new ExecFuture('rubocop --format=json --no-color ' . implode($paths, ' '));
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

  private function checkRubocopInstallation() {
    if (!Filesystem::binaryExists('rubocop')) {
      throw new ArcanistUsageException(
        pht('Rubocop is not installed, please run `gem install rubocop` or add it to your Bundler Gemfile')
      );
    }
  }

  protected function parseLinterOutput($output) {
    $json = json_decode($output, true);
    $files = $json['files'];

    $severityMap = array();
    $severityMap['refactor'] = 'warning';
    $severityMap['convention'] = 'warning';
    $severityMap['warning'] = 'warning';
    $severityMap['error'] = 'error';
    $severityMap['fatal'] = 'error';

    $messages = array();

    foreach ($files as $file) {
      foreach ($file['offenses'] as $offense) {
        $message = new ArcanistLintMessage();
        $message->setPath($file['path']);
        $message->setLine($offense['location']['line']);
        $message->setChar($offense['location']['column']);
        $message->setCode('RUBY');
        $message->setName($offense['cop_name']);
        $message->setDescription($offense['message']);
        $message->setseverity($severityMap[$offense['severity']]);

        $messages[] = $message;
      }
    }

    return $messages;
  }

}
