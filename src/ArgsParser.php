<?php

namespace WPSelenium;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Operand;
use GetOpt\Help;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

class ArgsParser{
    function __construct()
    {
        $this->getOpt = new GetOpt();
        $this->getOpt->addOperand((new Operand('browser', Operand::REQUIRED))
                    ->setDescription("Brower you want to test on. Chrome and Firefox supported by default. See documenation if you want to add others."));
        $this->getOpt->addOptions([
            Option::create(null, 'type', GetOpt::MULTIPLE_ARGUMENT)
                ->setDescription("The type of site you are testing. e.g --type wordpress"),
            Option::create(null, 'loglevel', GetOpt::MULTIPLE_ARGUMENT)
                ->setDescription("Console loglevel - info, warn, error, debug."),
            Option::create('?', 'help', GetOpt::NO_ARGUMENT)
                ->setDescription('Show this help and quit.'),
        ]);

        $this->getOpt->setHelp(new WPSeleniumHelp());
        $this->getOpt->getHelp()->setTexts(['example-text' => 'Example: ']);
        $this->ProcessInput();

    }

    public function GetOpts(){
        return $this->getOpt;
    }

    private function ProcessInput(){
        try {
            try {
                $this->getOpt->process(); 
            } catch (Missing $exception) {
                if (!$this->getOpt->getOption('help')) {
                    throw $exception;
                }
            }
        } catch (ArgumentException $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
            echo PHP_EOL . $this->getOpt->getHelpText();
            exit;
        }
        
        if ($this->getOpt->getOption('help')) {
            echo $this->getOpt->getHelpText();
            exit;
        }
    }
}



class WPSeleniumHelp extends Help {
    protected function renderUsage()
    {
        $usuage =   $this->getText('usage-title') .
                    $this->getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' ' .
                    $this->renderUsageOperands() . ' ' .
                    $this->renderUsageOptions() . PHP_EOL ;

        $example =  $this->getText('example-text') .
                    $this->getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' chrome --type wordpress' . PHP_EOL . PHP_EOL;

        return $usuage.$example;
    }

    protected function renderUsageOperands()
    {
        $usage = '';
        
        if ($this->getOpt->hasOperands()) {
            foreach ($this->getOpt->getOperandObjects() as $operand) {
                $name = $this->surround($operand->getName(), $this->texts['placeholder']);
                if (!$operand->isRequired()) {
                    $name = $this->surround($name, $this->texts['optional']);
                }
                $usage .= $name . ' ';
               
            }
        }

        return $usage;
    }
}





