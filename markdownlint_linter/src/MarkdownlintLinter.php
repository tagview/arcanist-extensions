<?php

final class MarkdownlintLinter extends ArcanistExternalLinter {
    private $config;

    public function getInfoName() {
        return 'Markdownlint';
    }

    public function getInfoDescription() {
        return pht('Uses Markdownlint for linting md files');
    }

    public function getInfoURI() {
        return 'https://github.com/markdownlint/markdownlint';
    }

    public function getLinterName() {
        return 'Markdownlint';
    }

    public function getLinterConfigurationName() {
        return 'markdownlint';
    }

    public function getDefaultBinary() {
        return 'mdl';
    }

    public function getInstallInstructions() {
      return pht("See https://github.com/markdownlint/markdownlint.\n".
        "If you face errors in OSX similar to: ' Operation not permitted - /usr/bin/mdl' \n".
        "Try installing it by using the following command:\n".
        "gem install -n /usr/local/bin/ mdl");
    }

    public function shouldExpectCommandErrors() {
        return true;
    }

    protected function getMandatoryFlags() {
      if ($this->config) {
        return array('--config='.$this->config);
      }

      return;
    }

    public function getLinterConfigurationOptions() {
      $options = array(
        'mdl.config' => array(
          'type' => 'optional string',
          'help' => pht('A custom configuration file.'),
        ),
      );

      return $options + parent::getLinterConfigurationOptions();
    }

    public function setLinterConfigurationValue($key, $value) {
      switch ($key) {
        case 'mdl.config':
          $this->config = $value;
          return;
      }

      return parent::setLinterConfigurationValue($key, $value);
    }

    protected function parseLinterOutput($path, $err, $stdout, $stderr) {
        $matches = array();
        $messages = array();

        $lines = explode("\n", $stdout);

        foreach($lines as $line)
          // Example: file.md:15: MD006 Consider starting bulleted lists at the beginning of the line
          if (preg_match('/^(.*):(\d+):\s(\w{2}\d{3})\s(.*)$/', $line, $matches)) {
              $message = new ArcanistLintMessage();
              $message->setName($matches[3]);
              $message->setDescription(trim($matches[4]));
              $message->setSeverity(ArcanistLintSeverity::SEVERITY_WARNING);

              $message->setPath(trim($matches[1]));
              $message->setLine((int)$matches[2]);

              $messages[] = $message;
          }

        return $messages;
    }
}
