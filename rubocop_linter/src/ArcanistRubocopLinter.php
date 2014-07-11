<?php

final class ArcanistRubocopLinter extends ArcanistExternalLinter {

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

  public function getDefaultBinary() {
    return 'rubocop';
  }

  public function getVersion() {
    list($stdout) = execx('%C --version', $this->getExecutableCommand());

    $matches = array();
    if (preg_match('/^(?P<version>\d+\.\d+\.\d+)$/', $stdout, $matches)) {
      return $matches['version'];
    } else {
      return false;
    }
  }

  public function getInstallInstructions() {
    return pht('Install Rubocop using `gem install rubocop`.');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  public function supportsReadDataFromStdin() {
    return false;
  }

  protected function getMandatoryFlags() {
    $options = array(
      '--format=json',
      '--no-color',
    );

    return $options;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $json = json_decode($stdout, true);
    $files = $json['files'];

    $severityMap = array();
    $severityMap['refactor'] = 'warning';
    $severityMap['convention'] = 'warning';
    $severityMap['warning'] = 'warning';
    $severityMap['error'] = 'error';
    $severityMap['fatal'] = 'errror';

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
