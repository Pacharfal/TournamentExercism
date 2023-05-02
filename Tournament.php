<?php

declare(strict_types=1);

use JetBrains\PhpStorm\Pure;

/**
 * Represents team in the tournament
 */
class Team
{
    private const WINS_DEFAULT = 0;
    private const MATCHES_DEFAULT = 0;
    private const LOSSES_DEFAULT = 0;
    private const DRAWS_DEFAULT = 0;
    private const POINTS_DEFAULT = 0;
    private const POINTS_WIN = 3;
    private const POINTS_DRAW = 1;

    protected string $name;
    protected int $matchesPlayed = self::MATCHES_DEFAULT;
    protected int $wins = self::WINS_DEFAULT;
    protected int $losses = self::LOSSES_DEFAULT;
    protected int $draws = self::DRAWS_DEFAULT;
    protected int $points = self::POINTS_DEFAULT;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMatchesPlayed(): int
    {
        return $this->matchesPlayed;
    }

    public function addMatchPlayed(): void
    {
        $this->matchesPlayed++;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function addWin(): void
    {
        $this->wins++;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function addLoss(): void
    {
        $this->losses++;
    }

    public function getDraws(): int
    {
        return $this->draws;
    }

    public function addDraw(): void
    {
        $this->draws++;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function addPoints(string $result): void
    {
        switch ($result):
            case 'win':
                $this->addMatchPlayed();
                $this->addWin();
                $this->points += self::POINTS_WIN;
                break;
            case 'loss':
                $this->addMatchPlayed();
                $this->addLoss();
                break;
            case 'draw':
                $this->addMatchPlayed();
                $this->addDraw();
                $this->points += self::POINTS_DRAW;
                break;
            default:
                echo 'Wrong input';
                break;
        endswitch;
    }

    #[Pure] public function getInfo(): string
    {
        return sprintf('%-31s| %2d | %2d | %2d | %2d | %2d', $this->getName(), $this->getMatchesPlayed(), $this->getWins(), $this->getDraws(), $this->getLosses(), $this->getPoints());
    }
}

class Tournament
{
    private const HEADER = 'Team                           | MP |  W |  D |  L |  P';
    /**
     * @var array<Team> $teams
     */
    protected array $teams = [];


    /**
     * @return array<Team>$teams
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @param array<Team> $teams
     */
    public function setTeams(array $teams): void
    {
        $this->teams = $teams;
    }

    protected function addTeam(Team $team): void
    {
        $teams = $this->getTeams();
        array_push($teams, $team);
        $this->setTeams($teams);
    }

    protected function checkTeams(string $teamA, string $teamB): void
    {
        $teams = $this->getTeams();
        $names = [];
        foreach ($teams as $team) {
            array_push($names, $team->getName());
        }

        if (!in_array($teamA, $names)) {
            $this->addTeam(new Team($teamA));
        }
        if (!in_array($teamB, $names)) {
            $this->addTeam(new Team($teamB));
        }
    }

    //sort teams by number of points, if they have same points, sort them by alphabet
    protected function sortTeams(): void
    {
        $teams = $this->getTeams();
        usort($teams, function ($a, $b) {
            if ($a->getPoints() == $b->getPoints()) {
                return ($a->getName() < $b->getName()) ? -1 : 1;
            }
            return ($a->getPoints() > $b->getPoints()) ? -1 : 1;
        });
        $this->setTeams($teams);
    }

    protected function playMatch(string $scores): void
    {
        list($teamA, $teamB, $result) = explode(';', $scores);

        $this->checkTeams($teamA, $teamB);
        $teams = $this->getTeams();
        foreach ($teams as $team) {
            if ($team->getName() === $teamA) {
                $team->addPoints($result);
            }
            if ($team->getName() === $teamB) {
                if ($result === 'win') {
                    $resultB = 'loss';
                    $team->addPoints($resultB);
                } elseif ($result === 'loss') {
                    $resultB = 'win';
                    $team->addPoints($resultB);
                } else {
                    $team->addPoints($result);
                }
            }
        }
        $this->setTeams($teams);
    }

    public function moreMatches(string $score): void
    {
        if (str_contains($score, "\n")) {
            $scores = explode("\n", $score);
            foreach ($scores as $score) {
                $this->playMatch($score);
            }
        } else {
            $this->playMatch($score);
        }
    }

    public function getScores(): string
    {
        $teams = $this->getTeams();
        $lastElement = end($teams);
        $board = sprintf("%-31s| %2s | %2s | %2s | %2s | %2s\n", 'Team', 'MP', 'W', 'D', 'L', 'P');
        foreach ($teams as $team) {
            if ($team != $lastElement) {
                $board .= $team->getInfo() . "\n";
            } else {
                $board .= $team->getInfo();
            }
        }
        return $board;
    }

    public function tally(string $scores): string
    {
        if ($scores === '') {
            return self::HEADER;
        } else {
            $this->moreMatches($scores);
            $this->sortTeams();
            return $this->getScores();
        }
    }
}


