<?php
$video = new Video('', '', $videos_id);
$filename = $video->getFilename();
//var_dump($filename);exit;
$vttfile = getVideosDir() . "{$filename}/{$filename}.vtt";
//echo $vttfile;
$hasTranscriptionFile = file_exists($vttfile);
$mp3file = AI::getLowerMP3($videos_id);
$mp3fileExists = file_exists($mp3file['path']);
$canTranscribe = false;
$columnCalbackFunctions = $hasTranscriptionFile ? [] : ['text'];
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <input class="form-control" id="searchInput" type="text" placeholder="Search Languages...">
    </div>
    <div class="panel-body" style="max-height: calc(100vh - 300px); overflow: auto;">
        <form id="languagesForm">
            <?php
            foreach ($global['langs_codes'] as $key => $value) {
                $checked = isset($_COOKIE['lang_' . $value['value']]) && $_COOKIE['lang_' . $value['value']] == 'true' ? 'checked' : '';
                echo "<div class=\"checkbox\">
                              <label>
                                  <input type=\"checkbox\" class=\"languageCheckbox\" data-lang-code=\"{$value['value']}\" value=\"{$value['label']}\" {$checked}>
                                  <i class=\"flagstrap-icon flagstrap-{$value['flag']}\"></i> {$value['label']}
                              </label>
                              <span id=\"progress{$value['value']}\" class=\"badge\" style=\"display:none;\">...</span>
                          </div>";
            }
            ?>
        </form>
    </div>
    <div class="panel-footer">
        <?php
        if (!$hasTranscriptionFile) {
        ?>
            <div class="alert alert-warning"><strong>Note:</strong> A transcription is required for translations. Currently, there is no transcription file associated with this video.</div>
        <?php
        } else {
        ?>
            <button class="btn btn-success btn-block" onclick="generateAITranslations()">
                <i class="fa-solid fa-language"></i> <?php echo __('Generate Translations') ?>
            </button>
        <?php
        }
        ?>
    </div>
</div>

<script>
    var hasTranscriptionRecord = false;

    function sortCheckboxes() {
        var $form = $('#languagesForm');
        var checkedCheckboxes = $form.find('.languageCheckbox:checked').closest('.checkbox').toArray();
        var uncheckedCheckboxes = $form.find('.languageCheckbox:not(:checked)').closest('.checkbox').toArray();

        // Append checked checkboxes at the beginning of the form
        checkedCheckboxes.forEach(function(checkbox) {
            $form.prepend(checkbox);
            getProgress($(checkbox).find('input').data('lang-code'));
        });

        // Append unchecked checkboxes after the checked ones
        uncheckedCheckboxes.forEach(function(checkbox) {
            $form.append(checkbox);
        });
    }

    function getTranslationCheckedValues() {
        var checkedValues = $('#languagesForm input[type="checkbox"]:checked').map(function() {
            return {
                code: $(this).data('lang-code'),
                name: $(this).val()
            };
        }).get();

        return (checkedValues);
    }

    async function generateAITranslations() {
        var langArray = getTranslationCheckedValues();
        modalContinueAISuggestions.showPleaseWait();

        for (let langArrayItem of langArray) {
            console.log(langArrayItem);
            try {
                await new Promise((resolve, reject) => {
                    $.ajax({
                        url: webSiteRootURL + 'plugin/AI/async.json.php',
                        data: {
                            videos_id: <?php echo $videos_id; ?>,
                            translation: 1,
                            lang: langArrayItem.code,
                            langName: langArrayItem.name
                        },
                        type: 'post',
                        success: function(response) {
                            if (response.error) {
                                avideoAlertError(response.msg);
                                reject(response.msg);
                            } else {
                                avideoToast(response.msg);
                                resolve();
                            }
                        },
                        error: function(xhr, status, error) {
                            avideoAlertError("AJAX request failed: " + error);
                            reject(error);
                        }
                    });
                });
            } catch (error) {
                console.error("Error in processing language:", langArrayItem, error);
                //break; // If you want to stop processing further languages upon error
            }
        }

        modalContinueAISuggestions.hidePleaseWait();
    }
    var progressTimeouts = {}; // Object to store timeouts for each language

    function getProgress(lang) {
        // Clear existing timeout for this language, if it exists
        if (progressTimeouts[lang]) {
            clearTimeout(progressTimeouts[lang]);
        }
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/progress.json.php',
            data: {
                type: '<?php echo AI::$typeTranslation; ?>',
                lang: lang,
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                if (response.error) {
                    //avideoAlertError(response.msg);
                } else {
                    console.log(response);
                    $('#progress' + response.lang).show();
                    $('#progress' + response.lang).html(response.msg);

                    if (response.timeout) {
                        // Set a new timeout for this language
                        progressTimeouts[lang] = setTimeout(function() {
                            getProgress(lang);
                        }, response.timeout);
                    }
                }
            }
        });
    }


    $(document).ready(function() {
        // Search filter
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#languagesForm div").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        // Save checkbox state
        $('.languageCheckbox').on('change', function() {
            var langCode = $(this).data('lang-code');
            var isChecked = $(this).is(':checked');
            Cookies.set('lang_' + langCode, isChecked, {
                path: '/',
                expires: 365
            });
            sortCheckboxes(); // Call the sorting function
        });
        sortCheckboxes();
    });
</script>