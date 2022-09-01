<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * blocks/ungraded_activities/lang/en_utf8/block_ungraded_activities.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

// essential strings
$string['pluginname'] = '未評定活動';
$string['blockdescription'] = 'このブロックではコースページに未評定活動のリストを表示します。';
$string['blockname'] = '未評定活動';
$string['blocknameplural'] = '未評定活動';

// roles strings
$string['ungraded_activities:addinstance'] = '新しい未評定活動ブロックを追加する';

// more strings
$string['adduserlinks'] = 'ユーザプロファイルへのリンクを追加する';
$string['adduserlinks_help'] = '**Yes**  
&nbsp; ユーザリストの名前は各ユーザのプロフィールページにリンクされます

**No**  
&nbsp; ユーザのプロフィールページへのリンクなしで、各ユーザの名前が表示されます。';
$string['apply'] = '適用する';
$string['applyselectedvalues'] = '選択した値を次のコースに適用します';
$string['clicktograde'] = 'この{$a}を評価するためにクリックしてください';
$string['customdatefmt'] = 'カスタム日付フォーマット文字列';
$string['checkoverrides'] = 'オーバーライドをチェックする';
$string['checkoverrides_help'] = 'この設定では上書きされた評点を評定表内に表示するかどうかを指定します。上書きされた評点は活動の通常の評定ページで修正することができませんので、この表示すると混乱する可能性があります。

この設定を有効にすると、データベースサーバに余分な作業が発生しますので、デフォルト設定は &quot;No&quot; です。

**No**  
&nbsp; Moodle評定表で活動の評点が上書きされたかどうかを確認しません。

**Yes - オーバーライドされた場合、無視する**  
&nbsp; 上書された評定は、提出のタイミングにも関わらず、無視します。

**Yes - 最近のオーバーライドを無視する**  
&nbsp; 提出後に評点が上書きされた場合、評点は無視します。上書の後に提出された場合、教師が評点を修正できるページへのリンクが表示されます。';
$string['checkoverrides1'] = 'Yes - 上書きされた場合は無視します。';
$string['checkoverrides2'] = 'Yes - 最近上書きされた場合は無視します';
$string['customdatefmt_help'] = 'ここで指定された文字列は、投稿日の書式設定に使用されます。

書式コードは、PHP の &quot;strftime&quot; 関数によって使用されるものです。これらのコードに関する詳細は、この設定のテキストボックスの横にある「&quot;Help&quot;」リンクから入手できます。';
$string['fixdaymonth'] = '日付の先頭の「0」を削除する';
$string['excludeemptysubmissions'] = '空の提出物を除外する';
$string['excludezerogradequestions'] = '０点問題を除外する';
$string['exportsettings'] = '設定のエクスポート';
$string['exportsettings_help'] = 'このリンクではこのブロックの設定をファイルにエクスポートし、他のコースにある同様のブロックにインポートすることができます。';
$string['fixdaymonth_help'] = '**Yes**  
&nbsp; 10未満の日および月番号の先頭の「０」を削除します

**No**  
&nbsp; 10未満の日および月番号を01、02、03等として表示します。';
$string['forexample'] = '例';
$string['head'] = '先頭';
$string['importsettings'] = '設定のインポート';
$string['importsettings_help'] = 'このリンクは他のコースで同じタイプのブロックからエクスポートされた構成設定ファイルから構成設定をインポートする画面に移動します。';
$string['includeemptysubmissions'] = '空の提出課題を含む';
$string['includezerogradequestions'] = '０点問題を含む';
$string['invalidblockname'] = 'ブロックインスタンスレコードのブロック名が無効です: id={$a->id}, blockname={$a->blockname}.';
$string['invalidcontextid'] = 'ブロックインスタンスレコードの無効な親コンテキストID: id = {$a->id}, parentcontextid = {$a->parentcontextid}';
$string['invalidcourseid'] = 'コースコンテキストレコード内の無効なインスタンスID：id={$a->id}, instanceid={$a->instanceid}';
$string['invalidimportfile'] = 'インポートファイルが無いか、空か、または無効でした';
$string['invalidinstanceid'] = '無効なブロックインスタンスID: id = {$a}';
$string['moodledatefmt'] = 'Moodle日付フォーマット文字列';
$string['moodledatefmt_help'] = '未評定提出物の日付はここで選択した日付と同様の方法でフォーマットされます。

日付の1つの横にある &quot;+&quot; 記号をクリックすると、その日付のフォーマットストリングの名前がフォーマットコードと共に表示されます。これは、下記の「&quot;カスタム日付形式文字列&quot;」の設定で独自の日付形式文字列を作成したい場合に便利です。

なお、&quot;Show date last modified&quot; が &quot;No&quot; に設定されている場合は、日付は表示されませ んのでご注意ください。また、&quot;カスタム日付フォーマット文字列&quot;の設定でフォーマットが指定されている場合、ここで選択された文字列が上書きされます。';
$string['mycourses'] = 'マイ・コース';
$string['mycourses_help'] ='このリストでは、このブロックの設定をコピーしたい他のコースを指定することができます。このリストにはあなたが教師で、すでに同様のブロックが設定されているコースのみ含まれます。';
$string['noactivities'] = '評定可能な活動なし';
$string['noitems'] = '未評定項目がありません';
$string['refreshthispage'] = 'このページを更新する';
$string['save'] = '保存　';
$string['settingsmenu'] = '設定メニュー';
$string['selectallnone'] = '選択';
$string['selectallnone_help'] = 'このコラムのチェックボックスではこのブロックの特定の設定を選択して、このサイトの他のMoodleコースにコピーすることができます。

設定は個別に選択するか、「すべて」または「なし」リンクを使用して、1クリックですべての設定またはすべての設定を選択することができます。

ブロック設定をコピーするコースを選択するには、このブロック設定ページの下部にあるコースメニューで行ってください。

あなたが教師 (または管理者) で、すでにTaskChainナビゲーションブロックが存在するコースにのみ、設定をコピーすることができます。

これらの設定を他のMoodleサイトのブロックにコピーするには、このページで「エクスポート」機能を使用し、コピー先のサイトではブロックの「インポート」機能を使用します。';
$string['showactivities'] = '活動を表示する';
$string['showactivities_help'] = 'ここで、評定、評価、または承認されていないアイテムのリストに含めたい活動のタイプをチェックボックスで選択することができます。';
$string['showassigns'] = '課題 (Moodle >= 2.3)';
$string['showassignments'] = '課題 (Moodle <= 2.2)';
$string['showattendances'] = '出席';
$string['showcountitems'] = '件数を表示する';
$string['showcountitems_help'] = '**Yes**  
&nbsp;見つかった未評定項目の総数と未評定項目を持つ活動の総数を示すメッセージを表示します。そのような活動ない場合、メッセージが表示されます。

**No**  
&nbsp; 未評定の項目と活動が見つかった数を示すメッセージを表示しないようにします。ただし、各活動ーの未評定アイテムの数は、活動ーへのリンクに表示されます。';
$string['showdatabases'] = 'データベース (評点あり)';
$string['showforums'] = 'フォーラム (評点あり)';
$string['showglossaries'] = '用語集 (評点あり)';
$string['showlessons'] = 'レッスン (エッセイ問題あり)';
$string['showquizzes'] = 'クイズ (エッセイ問題あり)';
$string['showquizzestext'] = 'クイズは活動のリストに含まれていません。';
$string['showtimes'] = '最終更新日を表示する';
$string['showtimes_help'] = '**Yes**  
&nbsp; 各未評定投稿に関する情報には投稿時刻が含まれます

**No**  
&nbsp; 未評定投稿の時刻に関する情報は表示されません。';
$string['showuserlist'] = 'ユーザ一覧の表示';
$string['showuserlist_help'] = '

**Yes - collapsed**  
&nbsp; それぞれの未評定活動に対して、このブロックは未評定アイテムを持つユーザのリストを表示せず、代わりにその活動のメイン評定ページへのリンクを表示します。

**Yes - collapsed**  
&nbsp; それぞれの未評定活動について、未評定提出物のユーザリストが作成されます。最初はリストは折りたたまれた状態で、ユーザ名は見えませんが、活動名をクリックすることでリストを拡張することができます。

**Yes - expanded**  
&nbsp; 各活動のユーザリストは完全に表示され、折りたたまれることはありません。';
$string['showuserlist1'] = 'Yes - 折りたたみ';
$string['showuserlist2'] = 'Yes - 展開';
$string['showusertype'] = 'リストに表示するユーザ';
$string['showusertype_help'] = '**すべてのユーザ**  

&nbsp; それぞれの活動に対するユーザリストは潜在的にその活動に対して何かを提出したすべてのユーザを含みます。

**このコースのすべての参加者**  
&nbsp; ユーザリストにはコースで現在ロールを持っているユーザのみ表示することができます。これは現在の管理者、教師、学生を含みますが、ゲストおよび元学生を含みません。

**このコースに登録されている学生**  
&nbsp; 現在コースの学生として登録されているユーザのみ、ユーザリストに表示することができます。管理者、教師、元学生、ゲストはユーザリストに表示されません。';
$string['showusertype0'] = 'すべてのユーザ';
$string['showusertype1'] = 'このコースのすべての参加者';
$string['showusertype2'] = 'このコースに登録されている学生';
$string['showworkshops'] = 'ワークショップ';
$string['tail'] = '後尾';
$string['textlength'] = '文字数';
$string['textlength_help'] = 'これらの設定は、ブロック内の1行に表示するには長すぎる課題名をどのようにフォーマットするかを指定します。

課題名の長さがここで指定した合計文字数を超える場合、HEAD … TAIL に再フォーマットされます（HEADは名前の先頭からの文字数、TAILは名前の後尾からの文字数）。

このコースで使用される各言語に対して、別々の値を指定できます。値を0にすると、この設定は事実上無効になることに注意してください。';
$string['title'] = '題名';
$string['title_help'] = 'このブロックの題名として表示される文字列です。このフィールドが空白の場合、ブロックのタイトルは表示されない。';
$string['total'] = '合計';
$string['ungradeditem'] = '{$a} 未評定';
$string['ungradeditems'] = '{$a} 未評定';
$string['ungradedactivity'] = '（{$a}つの活動中）';
$string['ungradedactivities'] = '（{$a}つの活動中）';
$string['validimportfile'] = '設定が正常にインポートされました';
