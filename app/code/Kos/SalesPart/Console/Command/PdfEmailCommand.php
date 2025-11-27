<?php
namespace Kos\SalesPart\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class PdfEmailCommand
 */
class PdfEmailCommand extends Command
{
    const NAME = 'quote_id';

    /**
     * @var \Kos\SalesPart\Model\Quote\Pdf
     */
    protected $pdfQuote;

    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    public $logger;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Kos\SalesPart\Model\Quote\PdfFactory $pdfQuote,
        LoggerInterface $logger,
        $name = null
    ) {
        parent::__construct($name);
        $this->quoteRepository = $quoteRepository;
        $this->pdfQuote = $pdfQuote;
        $this->date = $date;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $options = [
             new InputOption(
                 self::NAME,
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Quote ID'
             )
        ];
        $this->setDescription('This command will be generate pdf file.');
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($quoteID = $input->getOption(self::NAME)) {
            $output->writeln('<info>Quote ID is: `' . $quoteID . '`</info>');

            $quote = $this->getQuoteById($quoteID);

            $pdf = $this->pdfQuote->create()->getPdf([$quote]);
            $date = $this->date->date('Y-m-d_H-i-s');
            $this->fileFactory->create(
                __('test_pdf') . '_' . $date . '.pdf',
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        $output->writeln('<info>Success Message.</info>');
    }

    protected function getQuoteById($quoteId)
    {
        $quote = null;
        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            var_dump($exception->getMessage());
        }
        return $quote;
    }
}
