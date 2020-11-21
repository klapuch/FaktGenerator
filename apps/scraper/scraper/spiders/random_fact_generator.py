import scrapy
import logging
from scrapy.utils.project import get_project_settings
from typing import Dict, Optional, Iterable


settings = get_project_settings()


class RandomFactGenerator(scrapy.Spider):
    name = 'random_fact_generator'
    custom_settings = {
        'ITEM_PIPELINES': {
            'scraper.pipelines.CsvFilePipeline': 1,
        },
        'DOWNLOADER_MIDDLEWARES': {
            'scraper.middlewares.RandomUserAgentMiddleware': 1,
        },
    }
    FILENAME = settings['DATA_DIR'] + '/random_fact_generator.csv'
    NUMBER_OF_ATTEMPTS = 3000

    actual = 0

    def start_requests(self) -> scrapy.Request:
        for attempt in range(0, self.NUMBER_OF_ATTEMPTS):
            yield scrapy.Request(
                'http://randomfactgenerator.net/',
                callback=self.parse,
                dont_filter=True,
            )

    def parse(self, response: scrapy.http.Response) -> Iterable[Dict[str, Optional[str]]]:
        self.actual = self.actual + 1
        logging.info('Gathering facts (%d)', self.actual)
        for fact in response.xpath('//div[@id="f"]/div/text()[following-sibling::br]').getall():
            yield {'text': fact.replace('?', '')}
