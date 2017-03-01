<?php

final class JsonnetLinter extends ArcanistExternalLinter {

    public function getInfoName() {
        return 'Jsonnet';
    }

    public function getInfoDescription() {
        return pht('Jsonnet verifies jsonnet files execute correctly.');
    }

    public function getInfoURI() {
        return 'http://jsonnet.org/';
    }

    public function getLinterName() {
        return 'JSONNET';
    }

    public function getLinterConfigurationName() {
        return 'jsonnet';
    }

    public function getDefaultBinary() {
        return 'jsonnet';
    }

    public function getVersion() {
        list($err, $stdout, $stderr) = exec_manual(
            '%C --version',
            $this->getExecutableCommand());

        $matches = array();
        if (preg_match('/\bv(?P<version>\d+(?:\.\d+)+)\b/', $stdout, $matches)) {
            return $matches['version'];
        } else {
            return false;
        }
    }

    public function getInstallInstructions() {
        return pht('See https://github.com/google/jsonnet');
    }

    protected function getMandatoryFlags() {
        return array('eval');
    }

    public function shouldExpectCommandErrors() {
        return true;
    }

    protected function parseLinterOutput($path, $err, $stdout, $stderr) {
        $matches = array();

        //  STATIC ERROR: foo.jsonnet:10:14-27: Unknown variable: bar
        if (preg_match('/^STATIC ERROR:(.*):\(?(\d+):(\d+)\)?-\(?(?:\d+:)?\d+\)?: (.*)$/m', $stderr, $matches)) {
            $message = new ArcanistLintMessage();
            $message->setName('jsonnet static error');
            $message->setPath(trim($matches[1]));
            $message->setLine($matches[2]);
            $message->setChar($matches[3]);
            $message->setDescription(trim($matches[4]));
            $message->setSeverity(ArcanistLintSeverity::SEVERITY_ERROR);

            return [$message];
        }
        //  RUNTIME ERROR: Object assertion failed.
        //          mylib.libsonnet:49:12-44 thunk <object_assert>
        //          mylib.libsonnet:25:19-73 object <anonymous>
        //          foo.jsonnet:66:15-39  thunk <array_element>
        //          foo.jsonnet:66:14-40  object <anonymous>
        //          foo.jsonnet:(56:23)-(68:3)    object <anonymous>
        //          foo.jsonnet:(56:3)-(68:3)     thunk <array_element>
        //          During manifestation
        else if (preg_match('/^RUNTIME ERROR: (.*)\n\s+(.*):\(?(\d+):(\d+)\)?-\(?(?:\d+:)?\d+\)?\s+(.*)/m', $stderr, $matches)) {
            $message = new ArcanistLintMessage();
            $message->setName('jsonnet runtime error');
            $message->setDescription(trim($matches[1]));
            $message->setSeverity(ArcanistLintSeverity::SEVERITY_ERROR);

            $message->setPath(trim($matches[2]));
            $message->setLine($matches[3]);
            $message->setChar($matches[4]);
            $message->setCode($matches[5]);

            return [$message];
        }

        if ($err != 0) {
            // There was an error here, but we failed to find it
            return false;
        }

        // No errors!
        return [];
    }
}
