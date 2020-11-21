import re
import math
import scrapy
from scrapy.utils.project import get_project_settings
from typing import Dict, Optional, Iterable


settings = get_project_settings()


class WtfFunFact(scrapy.Spider):
    name = 'wtf_fun_fact'
    custom_settings = {
        'ITEM_PIPELINES': {
            'scraper.pipelines.CsvFilePipeline': 1,
        },
        'DOWNLOADER_MIDDLEWARES': {
            'scraper.middlewares.RandomUserAgentMiddleware': 1,
        },
    }
    FILENAME = settings['DATA_DIR'] + '/wtf_fun_fact.csv'

    DEFAULT_PER_PAGE = 10

    def start_requests(self) -> scrapy.Request:
        yield scrapy.Request('https://wtffunfact.com/', callback=self.main_page)

    def main_page(self, response: scrapy.http.Response) -> scrapy.Request:
        links = response.xpath('//aside[@id="categories-2"]//ul/li/a/@href').getall()
        counts = response.xpath('//aside[@id="categories-2"]//ul/li/text()').getall()
        for link, count in zip(links, counts):
            yield scrapy.Request(link, callback=self.facts_page)
            for page in range(2, WtfFunFact.get_last_page(int(re.sub(r'[^0-9]', '', count.strip())))):
                yield scrapy.Request('{0:s}/page/{1:d}/'.format(link.strip('/'), page), callback=self.facts_page)

    def facts_page(self, response: scrapy.http.Response) -> scrapy.Request:
        yield scrapy.Request(
            response.xpath('//article//header[@class="entry-header"]/h2/a/@href').get(),
            callback=self.parse,
        )

    def parse(self, response: scrapy.http.Response) -> Iterable[Dict[str, Optional[str]]]:
        yield {'text': ''.join(response.xpath('(//article//div[@class="entry-content"]/p)[1]/strong[text() = "– "]/preceding-sibling::node()').getall()).strip('\xa0').strip()}

    @staticmethod
    def get_last_page(count: int) -> int:
        return math.ceil(count / WtfFunFact.DEFAULT_PER_PAGE)
