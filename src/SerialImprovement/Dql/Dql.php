<?php
namespace SerialImprovement\Dql;

class Dql
{
    protected $patterns = [];

    public function interpret(string $query)
    {
        $querySegments = preg_split("/ THEN /", $query);

        $result = null;

        foreach ($querySegments as $querySegment) {

            // try to find matching patterns to fulfill this query segment
            foreach ($this->patterns as $pattern) {
                $matches = preg_match($pattern['regex'], $querySegment);

                if ($matches === 1) {
                    $args = [
                        '$prev' => $result
                    ];

                    foreach ($pattern['commands'] as $name => $condition) {
                        $reg = $this->buildCommandRegexPart($name, $condition);

                        $commandMatches = [];
                        preg_match_all("/$reg/", $querySegment, $commandMatches, PREG_SET_ORDER);

                        $args[$condition['name']] = trim($commandMatches[0][1], '"');
                    }

                    $result = $pattern['handler']($args);
                }
            }
        }

        return $result;
    }

    public function addPattern(string $pattern, callable $handler): void
    {

        $commands = $this->buildAst($pattern);

        $this->patterns[] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'commands' => $commands,
            'regex' => $this->buildPatternRegex($commands),
        ];

        print_r($this->patterns);
    }

    protected function buildAst(string $pattern)
    {
        $rawCommands = [];
        preg_match_all("/([[:upper:]]+)( \[[a-z:]+\])?/", $pattern, $rawCommands, PREG_SET_ORDER);

        $commands = [];
        foreach ($rawCommands as $rawCommand) {
            $commands[$rawCommand[1]] = $this->buildCondition($rawCommand[2]);
        }

        return $commands;
    }

    protected function buildCondition(?string $condition): ?array
    {
        if (is_null($condition)) {
            return null;
        }

        $condition = trim(trim($condition), '[]');
        list($type, $name) = explode(':', $condition);

        return [
            'type' => $type,
            'name' => $name,
        ];
    }

    protected function buildCommandRegexPart(string $name, ?array $condition): string
    {
        $regex = $name;

        switch ($condition['type']) {
            case 'str':
                $regex .= ' ("(?:[^"\\\]|\\\.)*")';
                break;
        }

        return $regex;
    }

    protected function buildPatternRegex(array $commands): string
    {
        $regexParts = [];

        foreach ($commands as $name => $condition) {
            $regexParts[] = $this->buildCommandRegexPart($name, $condition);
        }

        return '/' . implode(' ', $regexParts) . '/';
    }
}
