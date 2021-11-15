<?php

namespace SuplaBundle\Message;

use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EmailTwigExtension extends AbstractExtension {
    /** @var TranslatorInterface */
    private $translator;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(TranslatorInterface $translator, LocalSuplaCloud $localSuplaCloud, SuplaAutodiscover $autodiscover) {
        $this->translator = $translator;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->autodiscover = $autodiscover;
    }

    public function getFilters() {
        return [
            new TwigFilter('paragraph', [$this, 'generateParagraph'], ['is_safe' => ['html']]),
            new TwigFilter('greenButton', [$this, 'generateGreenButton'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions() {
        return [
            new TwigFunction('svrUrl', [$this->localSuplaCloud, 'getAddress']),
            new TwigFunction('cloudUrl', [$this, 'getCloudUrl']),
            new TwigFunction('cloudHost', [$this, 'getCloudHost']),
        ];
    }

    public function generateParagraph(string $text): string {
        $text = trim($text);
        return <<<PARAGRAPH
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">$text</p>
PARAGRAPH;
    }

    public function generateGreenButton(string $text, string $url): string {
        $copyMessage = 'If the link is not working, please copy and paste it or enter manually in a new browser window.'; // i18n
        $copyText = $this->generateParagraph($this->translator->trans($copyMessage));
        $copyText .= $this->generateParagraph("<code>$url</code>");
        return <<<GREENBUTTON
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
            <tbody>
            <tr>
                <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                        <tr>
                            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #00d151; border-radius: 5px; text-align: center;"> <a href="$url" target="_blank" style="display: inline-block; color: #ffffff; background-color: #00d151; border: solid 1px #00d151; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #00d151;">$text</a> </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        $copyText
GREENBUTTON;
    }

    public function getCloudUrl(): string {
        return $this->autodiscover->isBroker() ? 'https://cloud.supla.org' : $this->localSuplaCloud->getAddress();
    }

    public function getCloudHost(): string {
        return $this->autodiscover->isBroker() ? 'cloud.supla.org' : $this->localSuplaCloud->getHost();
    }
}
