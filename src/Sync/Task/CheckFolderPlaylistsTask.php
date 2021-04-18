<?php

namespace App\Sync\Task;

use App\Doctrine\ReloadableEntityManagerInterface;
use App\Entity;
use App\Flysystem\StationFilesystems;
use Azura\Files\ExtendedFilesystemInterface;
use Doctrine\ORM\Query;
use League\Flysystem\UnableToRetrieveMetadata;
use Psr\Log\LoggerInterface;

class CheckFolderPlaylistsTask extends AbstractTask
{
    protected Entity\Repository\StationPlaylistFolderRepository $folderRepo;

    protected Entity\Repository\StationPlaylistMediaRepository $spmRepo;

    public function __construct(
        ReloadableEntityManagerInterface $em,
        LoggerInterface $logger,
        Entity\Repository\StationPlaylistMediaRepository $spmRepo,
        Entity\Repository\StationPlaylistFolderRepository $folderRepo
    ) {
        parent::__construct($em, $logger);

        $this->spmRepo = $spmRepo;
        $this->folderRepo = $folderRepo;
    }

    public function run(bool $force = false): void
    {
        foreach ($this->iterateStations() as $station) {
            $this->syncPlaylistFolders($station);
        }
    }

    public function syncPlaylistFolders(Entity\Station $station): void
    {
        $this->logger->info(
            'Processing auto-assigning folders for station...',
            [
                'station' => $station->getName(),
            ]
        );

        $fsStation = new StationFilesystems($station);
        $fsMedia = $fsStation->getMediaFilesystem();

        $mediaInPlaylistQuery = $this->em->createQuery(
            <<<'DQL'
                SELECT spm.media_id
                FROM App\Entity\StationPlaylistMedia spm
                WHERE spm.playlist_id = :playlist_id
            DQL
        );

        $mediaInFolderQuery = $this->em->createQuery(
            <<<'DQL'
                SELECT sm.id
                FROM App\Entity\StationMedia sm
                WHERE sm.storage_location = :storageLocation
                AND sm.path LIKE :path
            DQL
        )->setParameter('storageLocation', $station->getMediaStorageLocation());

        foreach ($station->getPlaylists() as $playlist) {
            if (Entity\StationPlaylist::SOURCE_SONGS !== $playlist->getSource()) {
                continue;
            }

            $this->em->transactional(
                function () use ($station, $playlist, $fsMedia, $mediaInPlaylistQuery, $mediaInFolderQuery): void {
                    $this->processPlaylist(
                        $station,
                        $playlist,
                        $fsMedia,
                        $mediaInPlaylistQuery,
                        $mediaInFolderQuery
                    );
                }
            );
        }
    }

    protected function processPlaylist(
        Entity\Station $station,
        Entity\StationPlaylist $playlist,
        ExtendedFilesystemInterface $fsMedia,
        Query $mediaInPlaylistQuery,
        Query $mediaInFolderQuery
    ): void {
        $folders = $playlist->getFolders();
        if (0 === $folders->count()) {
            return;
        }

        // Get all media IDs that are already in the playlist.
        $mediaInPlaylistRaw = $mediaInPlaylistQuery->setParameter('playlist_id', $playlist->getId())
            ->getArrayResult();
        $mediaInPlaylist = array_column($mediaInPlaylistRaw, 'media_id', 'media_id');

        foreach ($folders as $folder) {
            $path = $folder->getPath();

            // Verify the folder still exists.
            try {
                $fsMedia->isDir($path);
            } catch (UnableToRetrieveMetadata $exception) {
                $this->em->remove($folder);
                continue;
            }

            $mediaInFolderRaw = $mediaInFolderQuery->setParameter('path', $path . '/%')
                ->getArrayResult();

            $addedRecords = 0;
            foreach ($mediaInFolderRaw as $row) {
                $mediaId = $row['id'];

                if (!isset($mediaInPlaylist[$mediaId])) {
                    $media = $this->em->find(Entity\StationMedia::class, $mediaId);
                    $this->spmRepo->addMediaToPlaylist($media, $playlist);

                    $mediaInPlaylist[$mediaId] = $mediaId;
                    $addedRecords++;
                }
            }

            $logMessage = (0 === $addedRecords)
                ? 'No changes detected in folder.'
                : sprintf('%d media records added from folder.', $addedRecords);

            $this->logger->debug(
                $logMessage,
                [
                    'playlist' => $playlist->getName(),
                    'folder' => $folder->getPath(),
                ]
            );
        }
    }
}
