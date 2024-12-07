<?php

namespace App\Console\Commands;

use EchoLabs\Prism\Exceptions\PrismException;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Text\Generator;
use EchoLabs\Prism\ValueObjects\Messages\UserMessage;
use Illuminate\Console\Command;
use EchoLabs\Prism\Enums\Provider;
use Illuminate\Support\Collection;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use function Laravel\Prompts\textarea;

class ai extends Command
{

    use Colors;
    use DrawsBoxes;

    private Collection $messages;

    public function __construct()
    {
        parent::__construct();
        $this->messages = collect();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws PrismException
     */
    public function handle()
    {
        $prism = $this->prismFactory();

        while (true){
            $this->chat($prism, $this->messages);
        }

    }

    /**
     * @throws PrismException
     */
    public function chat(Generator $prism, $messages): void
    {
        $message = textarea('Message');

        $this->messages->push(new UserMessage($message));

        $answer = $prism
            ->withMessages($this->messages->toArray())
            ->generate();

        $this->messages = $this->messages->merge($answer->responseMessages);

        $this->box('Response', wordwrap($answer->text, 60), color: 'magenta');

    }

    public function prismFactory(): Generator
    {
        return Prism::text()
            ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022');
    }
}
