<?php


use Snap\Core\Snap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\ChoiceQuestion;


@include_once __DIR__ . '/vendor/autoload.php';



class Install extends Command
{
    /**
     * Setup the command signature and help text.
     */
    protected function configure()
    {
        $this->setName('install');
    }

    /**
     * Run the command.
     *
     * @param  InputInterface  $input  Command input.
     * @param  OutputInterface $output Command output.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $this->write_intro($output);

        $question = new ChoiceQuestion(
            "\n<comment>Please choose a templating system for your theme:</comment>",
            [
                'Snap Default',
                'Blade'
            ]
        );

        $question->setErrorMessage('[%s] is invalid.');

        $response = $helper->ask($input, $output, $question);

        if ($response === 'Snap Default') {
            $output->writeln("<info>Setup finished.\nEnjoy SnapWP!</info>");
            exit;
        }

        if ($response === 'Blade') {
            // Install blade package
            $this->install_blade($output);
        }
    }

    private function install_blade(OutputInterface $output)
    {
        $install = new Process('composer require snapwp/snap-blade');

        $output->writeln("\n<comment>Downloading latest snapwp/snap-core package.\nPlease wait...</comment>");

        try {
            $install->mustRun(function ($type, $buffer) {
                echo ">>> $buffer";
            });

            $output->writeln('<info>Downloaded successfully!</info>');
        } catch (ProcessFailedException $exception) {
            $output->writeln("<error>Could not download:\n$exception->getMessage()</error>");
            exit;
        }

        $this->add_blade_to_config();

        $this->clear_templates();

        // Publish the snap package.
        $publish = new Process(
            sprintf('snap publish --package=\Snap\Blade\Blade_Service_Provider --root=%s', __DIR__)
        );

        try {
            $publish->mustRun();

            $output->writeln('<info>Blade package successfully published.</info>');
        } catch (ProcessFailedException $exception) {
            var_dump($exception);
            $output->writeln("<error>Could not download:\n$exception->()</error>");
            exit;
        }

    }

    /**
     * Output welcome message.
     *
     * @param OutputInterface $output
     */
    private function write_intro(OutputInterface $output)
    {
        $output->writeln('
 _______                     ________ ______ 
|     __|.-----.---.-.-----.|  |  |  |   __ \
|__     ||     |  _  |  _  ||  |  |  |    __/
|_______||__|__|___._|   __||________|___|   
                     |__|');

        $output->writeln("\nVersion " . Snap::VERSION);
    }

    /**
     * Clears all default templates from the theme.
     */
    private function clear_templates()
    {
        $dir_iterator = new RecursiveDirectoryIterator(__DIR__ . '/resources/templates');
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if (!is_dir($file) && strpos($file, '_example.php') === false) {
                @unlink($file);
            }
        }
    }

    /**
     * Add the provider to the services config.
     *
     * Crude but gets the job done.
     */
    private function add_blade_to_config()
    {
        $config = file_get_contents(__DIR__ . '/config/services.php');

        $providers = preg_replace(
            '/(\'providers\'\s*\=>\s*\[)([^]]*)(\])/m',
            "$1$2\tSnap\Blade\Blade_Service_provider::class,\n$3",
            $config
        );

        file_put_contents(__DIR__ . '/config/services.php', $providers);
    }
}


$application = new Application();
$application->add(new Install());

$application->run();