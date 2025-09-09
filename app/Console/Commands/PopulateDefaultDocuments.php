<?php

namespace App\Console\Commands;

use App\Models\Pilgrim;
use App\Models\PilgrimDocument;
use Illuminate\Console\Command;

class PopulateDefaultDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:populate-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate default document records for all existing pilgrims';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate default documents for existing pilgrims...');
        
        $pilgrims = Pilgrim::all();
        $this->info("Found {$pilgrims->count()} pilgrims.");
        
        $progressBar = $this->output->createProgressBar($pilgrims->count());
        $progressBar->start();
        
        $created = 0;
        $skipped = 0;
        
        foreach ($pilgrims as $pilgrim) {
            // Check if pilgrim already has documents
            $existingDocuments = $pilgrim->documents()->count();
            
            if ($existingDocuments === 0) {
                // Create default documents for this pilgrim
                PilgrimDocument::createDefaultDocumentsForPilgrim($pilgrim->id);
                $created++;
            } else {
                $skipped++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Process completed!");
        $this->info("📄 Created default documents for {$created} pilgrims");
        $this->info("⏭️  Skipped {$skipped} pilgrims (already have documents)");
        
        // Show summary of document types created
        $totalDocuments = PilgrimDocument::count();
        $this->info("📊 Total documents in system: {$totalDocuments}");
        
        // Show breakdown by document type
        $this->newLine();
        $this->info('📋 Document breakdown:');
        foreach (PilgrimDocument::DOCUMENT_TYPES as $type => $label) {
            $count = PilgrimDocument::where('document_type', $type)->count();
            $this->line("   {$label}: {$count}");
        }
        
        return Command::SUCCESS;
    }
}
