<?php

namespace SuplaBundle\Message;

use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Utils\StringUtils;
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
            new TwigFilter('unsubscribeLink', [$this, 'generateUnsubscribeLink'], ['needs_context' => true, 'is_safe' => ['html']]),
            new TwigFilter('linkButton', [$this, 'generateLinkButton'], ['needs_context' => true, 'is_safe' => ['html']]),
        ];
    }

    public function getFunctions() {
        return [
            new TwigFunction('svrUrl', [$this, 'getSvrUrl'], ['needs_context' => true]),
            new TwigFunction('cloudUrl', [$this, 'getCloudUrl'], ['needs_context' => true]),
            new TwigFunction('cloudHost', [$this, 'getCloudHost']),
        ];
    }

    public function generateParagraph(string $text): string {
        $text = trim($text);
        return <<<PARAGRAPH
        <p style="font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;">$text</p>
PARAGRAPH;
    }

    public function generateUnsubscribeLink(array $context, string $text): string {
        $url = $this->getCloudUrl([], 'account?optOutNotification=' . $context['templateName']);
        // @codingStandardsIgnoreStart
        $linkTag = '<a href="' . $url . '" target="_blank" style="text-decoration: underline; color: #999999; font-size: 12px; text-align: center;">$1</a>';
        // @codingStandardsIgnoreEnd
        $text = preg_replace('#\[(.+?)\]#', $linkTag, $text);
        return $text;
    }

    public function generateLinkButton(array $context, string $text, string $url, string $type = 'primary'): string {
        $copyMessage = 'If the link is not working, please copy and paste it or enter manually in a new browser window.'; // i18n
        $copyText = $this->generateParagraph($this->translator->trans($copyMessage, [], null, $context['userLocale']));
        $copyText .= $this->generateParagraph("<code>$url</code>");
        $color = ['danger' => '#d9534f'][$type] ?? '#00d151';
        // @codingStandardsIgnoreStart
        return <<<GREENBUTTON
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="butn butn-$type" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
            <tbody>
            <tr>
                <td align="left" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                        <tr>
                            <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: $color; border-radius: 5px; text-align: center;"> <a href="$url" target="_blank" style="display: inline-block; color: #ffffff; background-color: $color; border: solid 1px $color; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: $color;">$text</a> </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        $copyText
GREENBUTTON;
        // @codingStandardsIgnoreEnd
    }

    public function getCloudUrl(array $context, string $url = ''): string {
        $fullUrl = $this->autodiscover->isBroker() ? 'https://cloud.supla.org' : $this->localSuplaCloud->getAddress();
        if ($url) {
            $fullUrl = StringUtils::joinPaths($fullUrl, $url);
            if ($locale = $context['userLocale'] ?? null) {
                $fullUrl .= '?lang=' . $locale;
            }
        }
        return $fullUrl;
    }

    public function getCloudHost(): string {
        return $this->autodiscover->isBroker() ? 'cloud.supla.org' : $this->localSuplaCloud->getHost();
    }

    public function getSvrUrl(array $context, string $url = ''): string {
        $fullUrl = $this->localSuplaCloud->getAddress();
        if ($url) {
            $fullUrl = StringUtils::joinPaths($fullUrl, $url);
            if ($locale = $context['userLocale'] ?? null) {
                $fullUrl .= '?lang=' . $locale;
            }
        }
        return $fullUrl;
    }
}
