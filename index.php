<?php

enum GameMode: string 
{
    case TwoPlayers = 'Two Players';
    case AgainstMachine = 'Against Machine';
}

class Program 
{
    private bool $isRunning = false;
    private Game|null $game = null;

    public function __construct()
    {
        $this->game = new Game();
    }

    public function init(): void 
    {
        $this->isRunning = true;
        while ($this->isRunning === true) {
            $this->runMainMenu();
        }
    }

    private function runMainMenu(): void 
    {
        echo "\n| Menu Principal |";
        echo "\n| 1 | Jogar      |";
        echo "\n| 2 | Como Jogar |";
        echo "\n| 3 | Sair       |";
        echo "\n\n";
        $input = intval(readline("Digite a opção que deseja: "));

        if (!in_array($input, [1, 2, 3])) {
            echo "\nOpção inválida!\n";
            return;
        }

        if ($input === 1) {
            $this->runModesMenu();
            return;
        }
        
        if ($input === 2) {
            echo "\nSempre quando for sua vez, digite um dos números de 1 à 9 correspondente à casa que deseja preencher, como desenhado abaixo:";
            echo "\n";
            echo "\n1 | 2 | 3";
            echo "\n4 | 5 | 6";
            echo "\n7 | 8 | 9";
            echo "\n\n";
            readline("Pressione enter para voltar.");
            return;
        }
        
        if ($input === 3) {
            echo "\nObrigado por jogar! Volte sempre.\n";
            $this->isRunning = false;
            return;
        }
    }

    private function runModesMenu(): void 
    {
        echo "\n| Modo de Jogo         |";
        echo "\n| 1 | Dois Jogadores   |";
        echo "\n| 2 | Contra a Máquina |";
        echo "\n| 3 | Voltar           |";
        echo "\n\n";
        $input = intval(readline("Digite a opção que deseja: "));

        if (!in_array($input, [1, 2, 3])) {
            echo "\nOpção inválida!\n";
            return;
        }

        if ($input === 1) {
            $this->game->setMode(GameMode::TwoPlayers);
            $this->game->start();
            return;
        } 
        
        if ($input === 2) {
            $this->game->setMode(GameMode::AgainstMachine);
            $this->game->start();
            return;
        }
        
        if ($input === 3) {
            return;
        }
    }
}

class Game 
{
    private GameMode|null $mode = null;
    private bool $onGoing = false;
    private int $playerOfTheTurn = 1;
    private int|null $result = null;
    private array $board = [
        [" ", " ", " "],
        [" ", " ", " "],
        [" ", " ", " "]
    ];

    public function setMode(GameMode $mode): void 
    {
        $this->mode = $mode;
    }

    public function start(): void 
    {
        $this->onGoing = true;
        while ($this->onGoing === true) {
            $this->draw();

            if (!$this->hasResult()) {
                $this->runMarkBoard();
                $this->endTurn();
                $this->updateResult();
            } else {
                $this->printResult();
                $this->endGame();
            }
        }
    }

    private function draw(): void 
    {
        $lines = array_map(fn ($line) => implode(" | ", $line), $this->board);
        echo "\n" . implode("\n", $lines) . "\n";
    }

    private function runMarkBoard(): void 
    {
        $emptyCells = $this->getEmptyCells();
        $marker = $this->playerOfTheTurn === 1 ? "O" : "X";
        $isMachineTurn = $this->playerOfTheTurn === 2 && $this->mode === GameMode::AgainstMachine;

        $wasMarked = false;
        while ($wasMarked === false) {
            echo "\n";
            $cell = $isMachineTurn 
                ? $emptyCells[array_rand($emptyCells)]
                : readline("Escolha onde deseja marcar o {$marker} (opções: " . implode(", ", $emptyCells) . "): ");
    
            if ($isMachineTurn) {
                echo "\nMáquina está pensando...\n";
                sleep(1);
            }
    
            if (!in_array($cell, $emptyCells)) {
                echo "\nOpção inválida!\n";
            } else {
                $wasMarked = true;
            }
        }

        $this->markBoardByCell($cell, $marker);
    }

    private function getEmptyCells(): array 
    {
        $emptyCells = [];
        foreach ($this->board as $lineIndex => $line) {
            foreach ($line as $cellIndex => $cell) {
                if ($cell === " ") {
                    $emptyCells[] = $lineIndex * 3 + ($cellIndex + 1);
                }
            }
        }
        return $emptyCells;
    }

    private function markBoardByCell(int $cell, string $marker): void 
    {
        $baseCell = $cell - 1;
        $lineIndex = intdiv($baseCell, 3);
        $columnIndex = $baseCell % 3;
        
        $this->board[$lineIndex][$columnIndex] = $marker;
    }

    private function endTurn(): void
    {
        $this->playerOfTheTurn = $this->playerOfTheTurn === 1 ? 2 : 1;
        return;
    }

    private function updateResult(): void 
    {
        if (count($this->getEmptyCells()) === 0) {
            $this->result = 0;
            return;
        }

        if ($this->isAnyLineFormedWithTheMarker("O")) {
            $this->result = 1;
            return;
        }

        if ($this->isAnyLineFormedWithTheMarker("X")) {
            $this->result = 2;
            return;
        }
    }

    private function isAnyLineFormedWithTheMarker(string $marker): bool
    {
        $board = $this->board;
        if (
            in_array([$marker, $marker, $marker], $board) ||
            array_map(fn($line) => $line[0], $board) === [$marker, $marker, $marker] || 
            array_map(fn($line) => $line[1], $board) === [$marker, $marker, $marker] || 
            array_map(fn($line) => $line[2], $board) === [$marker, $marker, $marker] || 
            ($board[0][0] === $marker && $board[1][1] === $marker && $board[2][2] === $marker) || 
            ($board[0][2] === $marker && $board[1][1] === $marker && $board[2][0] === $marker)
        ) {
            return true;
        }

        return false;
    }

    private function hasResult(): bool 
    {
        return $this->result !== null;
    }

    private function printResult(): void 
    {
        echo "\n";
        if ($this->result === 0) {
            echo "Deu velha!";
        } else if ($this->result === 1) {
            echo "O primeiro jogador venceu!";
        } else if($this->result === 2) {
            echo "O segundo jogador venceu!";
        }

        echo "\n";
    }

    private function endGame(): void 
    {
        $this->onGoing = false;
        $this->mode = null;
        $this->playerOfTheTurn = 1;
        $this->result = null;
        $this->board = [
            [" ", " ", " "],
            [" ", " ", " "],
            [" ", " ", " "]
        ];
    }
}

$program = new Program();
$program->init();