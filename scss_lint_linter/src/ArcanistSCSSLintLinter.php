<?php
// Copied from https://secure.phabricator.com/D10741
// We should remove when it's merged in
final class ArcanistSCSSLintLinter extends ArcanistExternalLinter {

  private $config;

  public function getInfoName() {
    return 'SCSS-Lint';
  }

  public function getInfoURI() {
    return 'https://github.com/causes/scss-lint';
  }

  public function getInfoDescription() {
    return pht(
      'scss-lint is a tool to help keep your SCSS files clean and readable.');
  }

  public function getLinterName() {
    return 'SCSS-Lint';
  }

  public function getLinterConfigurationName() {
    return 'scss-lint';
  }

  public function getDefaultBinary() {
    return 'scss-lint';
  }

  public function getVersion() {
    list($stdout) = execx('%C --version', $this->getExecutableCommand());

    $matches = array();
    if (preg_match('/^scss-lint\s(?P<version>\d+\.\d+\.\d+)$/', $stdout,
        $matches)) {
      return $matches['version'];
    } else {
      return false;
    }
  }

  public function getInstallInstructions() {
    return pht('Install SCSS-Lint using `gem install scss-lint`.');
  }

  public function shouldExpectCommandErrors() {
    return true;
  }

  public function supportsReadDataFromStdin() {
    return false;
  }

  protected function getMandatoryFlags() {
    $options = array(
      '--format=JSON',
    );

    if ($this->config) {
      $options[] = '--config='.$this->config;
    }

    return $options;
  }

  public function getLinterConfigurationOptions() {
    $options = array(
      'scss-lint.config' => array(
        'type' => 'optional string',
        'help' => pht('A custom configuration file.'),
      ),
    );

    return $options + parent::getLinterConfigurationOptions();
  }

  public function setLinterConfigurationValue($key, $value) {
    switch ($key) {
      case 'scss-lint.config':
        $this->config = $value;
        return;
    }

    return parent::setLinterConfigurationValue($key, $value);
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $results = json_decode($stdout);
    $messages = array();

    foreach ($results as $path => $offenses) {
      foreach ($offenses as $offense) {
        $message = new ArcanistLintMessage();

        $message->setPath($path);
        $message->setDescription($offense->reason);

        $message->setLine($offense->line);
        $message->setChar($offense->column);

        $message->setSeverity($this->getArcSeverity($offense->severity));
        $message->setName($this->getLinterName());
        $message->setCode($offense->linter);

        $messages[] = $message;
      }
    }

    return $messages;
  }

  private function getArcSeverity($severity) {
    $arc_severity = ArcanistLintSeverity::SEVERITY_ADVICE;

    switch ($severity) {
      case 'warning':
        $arc_severity = ArcanistLintSeverity::SEVERITY_WARNING;
        break;

      case 'error':
        $arc_severity = ArcanistLintSeverity::SEVERITY_ERROR;
        break;
    }

    return $arc_severity;
  }
}
