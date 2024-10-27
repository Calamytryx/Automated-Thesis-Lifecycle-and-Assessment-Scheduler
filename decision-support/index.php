<?php

define('TITLE', "Defense");
include '../assets/layouts/header.php';

?>



<main role="main">

    <section class="jumbotron text-center py-5">
        <div class="container">
            <h1 class="jumbotron-heading mb-4">Research Title here</h1>
            <p class="text-muted">
                Members here Members here Members here Members here
                <hr class="my-3">
                Advicer here
                <hr class="my-3">
                Course here
            </p>
        </div>
    </section>

    <div class="album py-5">
        <div class="container">

            <div class="text-center text-muted mb-5">
                <h2>The full paper Goes here</h2>
                <hr>
            </div>

            <div class="row">
                <div class="col-md12">
                    <div class="card mb box-shadow">
                        <div class="card-body">
                            <p class="card-text">PDF VIEW</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <embed id="pdf" src="https://www.officialgazette.gov.ph/downloads/1997/06jun/19970606-RA-8293-FVR.pdf" type="application/pdf" style="width: 100%; height: 100%;">
                            </div>
                            <small class="text-muted">[under development]</small>
                        </div>
                    </div>
                </div>
            </div>
<!-- title proposal -->
            <div class="col-md12">
                <div class="card mb box-shadow">
                    <div class="card-body">
                        <p class="card-text">Title Proposal Defense Score Sheet</p>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th colspan="5">CATEGORY</th>
                                    <th colspan="2">RATING</th>
                                    <th>MAXIMUM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th colspan="5">RESEARCH TOPIC</th>
                                    <th colspan="3">Group Grade</th>
                                </tr>
                                <tr>
                                    <th colspan="8">Significance of the Project</th>
                                </tr>
                                <tr>
                                    <td colspan="5">Appealing (generate interests from the end-user)</td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Usefulness (meet the needs of the end-users)</td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <th colspan="8">Innovative</th>
                                </tr>
                                <tr>
                                    <td colspan="5">Concept of the project shall be original or an enhancement of an existing technology</td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="20"></td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <th colspan="8">Organization</th>
                                </tr>
                                <tr>
                                    <td colspan="5">Presentation and development of ideas are <i>clear</i> and <i>logical</i></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="20"></td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <th colspan="5">Subtotal Group Grade</th>
                                    <th colspan="2"></th>
                                    <th>60%</th>
                                </tr>
                                <tr>
                                    <td colspan="8"></td>
                                </tr>
                                <tr>
                                    <th>ORAL DEFENSE</th>
                                    <th colspan="7">Individual Grades</th>
                                </tr>
                                <tr>
                                    <th>Presentation</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th colspan="2">D</th>
                                    <th>E</th>
                                    <th>10%</th>
                                </tr>
                                <tr>
                                    <td>Time allotted for presentation are met</td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>The visual presentation exemplified ideas, <i>concisely</i> and <i>comprehensively</i></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="5"></td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <th>Delivery</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th colspan="2">D</th>
                                    <th>E</th>
                                    <th>30%</th>
                                </tr>
                                <tr>
                                    <td>Presentor is well prepared, appeared relaxed and confident </td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Presentor is able to communicate effectively the ideas</td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Exemplified mastery and reasoning ability in defending his/her proposal/section</td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td colspan="2"><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td><input type="number" class="form-control" placeholder="Rating" min="0" max="10"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <th>Subtotal Individual Grade</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th colspan="2"></th>
                                    <th></th>
                                    <th>40%</th>
                                </tr>
                                <tr>
                                    <th>TOTAL (Please add the group grade [60%] to the individual grades [40%])</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th colspan="2"></th>
                                    <th></th>
                                    <th>100%</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Title Proposal Defense Evaluation Sheet</p>
                        <textarea class="form-control" rows="3" placeholder="Comments, Evaluation and Recommendations"></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-outline-secondary">Submit</a>
                            </div>
                            <small class="text-muted">1/3 Panelist Complete</small>
                        </div>
                        <div class="col-md12">
                            <div class="card mb box-shadow">
                                <div class="card-body">
                                    <p class="card-text">AI Analysis here</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">[under development]</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        different page admin side
                        <p class="card-text">Title Proposal</p>
                        <input type="text" class="form-control" placeholder="Working title 1">
                        <textarea class="form-control" rows="3" placeholder="Objectives of the project"></textarea>
                        <input type="text" class="form-control" placeholder="Working title 2">
                        <textarea class="form-control" rows="3" placeholder="Objectives of the project"></textarea>
                        <input type="text" class="form-control" placeholder="Working title 3">
                        <textarea class="form-control" rows="3" placeholder="Objectives of the project"></textarea>
                        <div class="col-md12">
                            <div class="card mb box-shadow">
                                <div class="card-body">
                                    <p class="card-text">Approved Working Title</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                    <select class="form-control">
                                        <option disabled selected hidden>Select Approved Title</option>
                                        <option value="Working title 1">Working title 1</option>
                                        <option value="Working title 2">Working title 2</option>
                                        <option value="Working title 3">Working title 3</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- thesis1 proposal -->
            <!-- <div class="col-md12">
                <div class="card mb box-shadow">
                    <div class="card-body">
                        <p class="card-text">Thesis Proposal</p>
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th colspan="5">CATEGORY</th>
                                    <th colspan="2">RATING</th>
                                    <th>MAXIMUM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5">RESEARCH TOPIC</td>
                                    <td colspan="3">Group Grade</td>
                                </tr>
                                <tr>
                                    <td colspan="8">Significance of the Project</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Appealing (generate interests from the end-user)</td>
                                    <td colspan="2"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Usefulness (meet the needs of the end-users)</td>
                                    <td colspan="2"></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td colspan="8">Innovative</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Concept of the project shall be original or an enhancement of an existing technology</td>
                                    <td colspan="2"></td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <td colspan="8">Organization</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Presentation and development of ideas are <i>clear</i> and <i>logical</i></td>
                                    <td colspan="2"></td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <td colspan="5">Subtotal Group Grade</td>
                                    <td colspan="2"></td>
                                    <td>60%</td>
                                </tr>
                                <tr>
                                    <td colspan="8"></td>
                                </tr>
                                <tr>
                                    <td>ORAL DEFENSE</td>
                                    <td colspan="7">Individual Grades</td>
                                </tr>
                                <tr>
                                    <td>Presentation</td>
                                    <td>A</td>
                                    <td>B</td>
                                    <td>C</td>
                                    <td colspan="2">D</td>
                                    <td>E</td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Time allotted for presentation are met</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>The visual presentation exemplified ideas, <i>concisely</i> and <i>comprehensively</i></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>Delivery</td>
                                    <td>A</td>
                                    <td>B</td>
                                    <td>C</td>
                                    <td colspan="2">D</td>
                                    <td>E</td>
                                    <td>30%</td>
                                </tr>
                                <tr>
                                    <td>Presentor is well prepared, appeared relaxed and confident </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Presentor is able to communicate effectively the ideas</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Exemplified mastery and reasoning ability in defending his/her proposal/section</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Subtotal Individual Grade</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>40%</td>
                                </tr>
                                <tr>
                                    <td>TOTAL (Please add the group grade [60%] to the individual grades [40%])</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2"></td>
                                    <td></td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Comments</p>
                        <textarea class="form-control" rows="3" placeholder="Add your comments here..."></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-outline-secondary">Submit</a>
                            </div>
                            <small class="text-muted">1/3 Panelist Complete</small>
                        </div>
                        <div class="col-md12">
                            <div class="card mb box-shadow">
                                <div class="card-body">
                                    <p class="card-text">AI Analysis here</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">[under development]</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
                        

</main>


<?php

include '../assets/layouts/footer.php'

?>